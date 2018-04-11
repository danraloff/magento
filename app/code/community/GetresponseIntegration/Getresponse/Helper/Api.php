<?php

/**
 * Class GetresponseIntegration_Getresponse_Helper_Api
 */
class GetresponseIntegration_Getresponse_Helper_Api
{
    const CONTACT_ERROR = 1;
    const CONTACT_UPDATED = 2;
    const CONTACT_CREATED = 3;
    const ORIGIN_NAME = 'magento';

    const GET_AUTORESPONDER_CACHE_KEY = 'get_autoresponders';
    const GET_CAMPAIGN_CACHE_KEY = 'get_campaign';
    const CONTACT_CACHE_KEY = 'get_campaign';
    const GET_FROM_FIELDS_CACHE_KEY = 'get_from_fields';
    const GET_CONFIRMATIONS_SUBJECT = 'get_confirmations_subject';
    const GET_CONFIRMATIONS_BODY = 'get_confirmations_body';
    const GET_PUBLISHED_FORMS = 'get_published_forms';
    const GET_PUBLISHED_WEB_FORMS = 'get_published_web_forms';
    const GET_SHOPS = 'get_shops';
    const PRODUCT_CACHE_KEY = 'product';

    public static $status
        = array(
            self::CONTACT_CREATED => 'Created',
            self::CONTACT_UPDATED => 'Updated',
            self::CONTACT_ERROR   => 'Not added'
        );

    /** @var array */
    private $cachedCustoms = array();

    /** @var GetresponseIntegration_Getresponse_Model_Cache */
    protected $cache;

    public function __construct()
    {
        $this->cache = Mage::getSingleton('getresponse/cache');
    }

    /**
     * Getresponse API instance
     */
    public static function grapi()
    {
        return self::instance();
    }

    /**
     * @return GetresponseIntegration_Getresponse_Helper_GrApi
     */
    public static function instance()
    {
        static $instance;

        if (null === $instance) {
            Mage::getResourceHelper('getresponse/grapi');
            $instance = new GetresponseIntegration_Getresponse_Helper_GrApi();
        }

        return $instance;
    }

    /**
     * @param $email
     * @param $campaign
     *
     * @return mixed
     */
    public function getContact($email, $campaign)
    {
        $contact = $this->cache->load(md5(self::CONTACT_CACHE_KEY . $email));

        if (!empty($contact)) {
            return $contact;
        }

        $results = (array)$this->grapi()->get_contacts(
            array('query' => array(
                    'email'      => $email,
                    'campaignId' => $campaign
                ), 'additionalFlags' => 'forceCustoms'
            )
        );

        $contact = reset($results);
        $this->cache->save($contact, md5(self::CONTACT_CACHE_KEY . $email));

        return $contact;
    }

    /**
     * Get all custom fields.
     *
     * @return array
     */
    public function getCustomFields()
    {
        $all_customs = array();
        $results = $this->grapi()->get_custom_fields(array('perPage' => 1000));

        if (empty($results)) {
            return $all_customs;
        }

        foreach ($results as $ac) {
            if (isset($ac->name) && isset($ac->customFieldId)) {
                $all_customs[$ac->name] = $ac->customFieldId;
            }
        }

        return $all_customs;
    }

    /**
     * Set customs.
     *
     * * @param $user_customs
     *
     * @return array
     */
    public function setCustoms($user_customs)
    {
        $custom_fields = array();

        if (empty($user_customs)) {
            return $custom_fields;
        }

        foreach ($user_customs as $name => $value) {

            if (!isset($this->cachedCustoms[$name])) {

                $customs = (array)$this->grapi()->get_custom_fields(
                    array('query[name]' => $name)
                );
                $custom = reset($customs);

                // custom field not found - create new
                if (empty($custom) || empty($custom->customFieldId)) {
                    $custom = $this->grapi()->add_custom_field(
                        array(
                            'name'   => $name,
                            'type'   => is_array($value) ? "checkbox" : "text",
                            'hidden' => "false",
                            'values' => is_array($value) ? $value
                                : array($value),
                        )
                    );
                    // Custom adding failed
                    if (!isset($custom->customFieldId)) {
                        continue;
                    }
                }

                $this->cachedCustoms[$name] = $custom;

            } else {
                $custom = $this->cachedCustoms[$name];
            }

            $custom_fields[] = array(
                'customFieldId' => $custom->customFieldId,
                'value'         => is_array($value) ? $value : array($value)
            );
        }

        return $custom_fields;
    }

    /**
     * Merge account custom fields.
     *
     * @param array $results      results.
     * @param array $user_customs user customs.
     *
     * @return array
     */
    public function mergeUserCustoms($results, $user_customs)
    {
        $custom_fields = array();

        if (is_array($results)) {
            foreach ($results as $customs) {
                $value = $customs->value;
                if (in_array($customs->name, array_keys($user_customs))) {
                    $user_custom_value = $user_customs[$customs->name];
                    $value = is_array($user_custom_value) ? $user_custom_value
                        : array($user_custom_value);
                    unset($user_customs[$customs->name]);
                }

                $custom_fields[] = array(
                    'customFieldId' => $customs->customFieldId,
                    'value'         => $value,
                );
            }
        }

        return array_merge($custom_fields, $this->setCustoms($user_customs));
    }

    /**
     * Get getresponse campaigns from user account
     */
    public function getGrCampaigns()
    {
        $cachedValue = $this->cache->load(self::GET_CAMPAIGN_CACHE_KEY);
        if (false !== $cachedValue) {
            return $cachedValue;
        }

        $campaigns = array();
        $page = 1;
        $perPage = 100;

        do {
            $apiResponse = $this->grapi()->get_campaigns(
                array(
                    'sort'    => array('name' => 'asc'),
                    'page'    => $page,
                    'perPage' => $perPage
                )
            );

            if (isset($apiResponse->codeDescription)) {
                $apiResponse = array();
            }

            foreach ($apiResponse as $result) {
                $campaigns[$result->campaignId] = $result->name;
            }

            $page++;
        } while (count((array)$apiResponse) === $perPage);

        $this->cache->save($campaigns, self::GET_CAMPAIGN_CACHE_KEY);

        return $campaigns;
    }

    /**
     * Get getresponse campaigns from user account
     */
    public function getPublishedForms()
    {
        $cachedValue = $this->cache->load(self::GET_PUBLISHED_FORMS);
        if (false !== $cachedValue) {
            return $cachedValue;
        }

        $results = $this->grapi()->get_forms();
        if (empty($results) || isset($results->codeDescription)) {
            return false;
        }

        $forms = array();

        foreach ($results as $form) {
            if (isset($form->status) && 'published' === $form->status) {
                $forms[] = $form;
            }
        }

        $this->cache->save($forms, self::GET_PUBLISHED_FORMS);

        return $forms;
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getWebform($id)
    {
        return $this->grapi()->get_web_form($id);
    }

    public function getForm($id)
    {
        return $this->grapi()->get_form($id);
    }

    public function getPublishedWebForms()
    {
        $cachedValue = $this->cache->load(self::GET_PUBLISHED_WEB_FORMS);
        if (false !== $cachedValue) {
            return $cachedValue;
        }

        $results = $this->grapi()->get_web_forms();
        if (empty($results) || isset($results->codeDescription)) {
            return false;
        }

        $forms = array();

        foreach ($results as $webform) {
            if (isset($webform->status) && $webform->status == 'enabled') {
                $forms[] = $webform;
            }
        }

        $this->cache->save($forms, self::GET_PUBLISHED_WEB_FORMS);

        return $forms;
    }

    /**
     * @return array|bool
     */
    public function getCampaignDays()
    {
        $cachedValue = $this->cache->load(self::GET_AUTORESPONDER_CACHE_KEY);
        if (false !== $cachedValue) {
            return $cachedValue;
        }

        $campaignDays = array();
        $page = 1;
        $perPage = 100;

        do {
            $apiResponse = $this->grapi()->get_autoresponders(
                array('page' => $page, 'perPage' => $perPage)
            );

            if (isset($apiResponse->codeDescription)) {
                $apiResponse = array();
            }

            foreach ($apiResponse as $autoresponder) {

                if ($autoresponder->triggerSettings->dayOfCycle == null) {
                    continue;
                }

                $campaignDays[$autoresponder->triggerSettings->subscribedCampaign->campaignId][$autoresponder->autoresponderId]
                    = array(
                    'day'    => $autoresponder->triggerSettings->dayOfCycle,
                    'name'   => $autoresponder->name,
                    'status' => $autoresponder->status,
                );
            }

            $page++;
        } while (count((array)$apiResponse) === $perPage);

        $this->cache->save($campaignDays, self::GET_AUTORESPONDER_CACHE_KEY);

        return $campaignDays;
    }

    /**
     * Add camapaign to GetResponse via API
     *
     * @param $campaign_name
     * @param $from_field
     * @param $reply_to_field
     * @param $confirmation_subject
     * @param $confirmation_body
     *
     * @return string
     */
    public function addCampaignToGR(
        $campaign_name,
        $from_field,
        $reply_to_field,
        $confirmation_subject,
        $confirmation_body
    ) {
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $code = strtoupper(substr($locale, 0, 2));

        try {
            $params = array(
                'name'         => $campaign_name,
                'confirmation' => array(
                    'fromField'                         => array('fromFieldId' => $from_field),
                    'replyTo'                           => array('fromFieldId' => $reply_to_field),
                    'subscriptionConfirmationBodyId'    => $confirmation_body,
                    'subscriptionConfirmationSubjectId' => $confirmation_subject
                ),
                'languageCode' => $code
            );

            $result = $this->grapi()->create_campaign($params);
            $this->cache->remove(self::GET_CAMPAIGN_CACHE_KEY);

            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $contact_id
     *
     * @return bool|mixed
     */
    public function deleteContact($contact_id)
    {
        $results = $this->grapi()->delete_contact($contact_id);
        if (!empty($results) && !isset($results->codeDescription)) {
            return $results;
        }

        return false;
    }

    /**
     * Set details for gr api
     *
     * @param $api_key
     * @param $api_url
     * @param $api_domain
     */
    public function setApiDetails($api_key, $api_url, $api_domain)
    {
        if (!empty($api_key)) {
            $this->grapi()->api_key = $api_key;
        }

        if (!empty($api_url)) {
            $this->grapi()->api_url = $api_url;
        }

        if (!empty($api_domain)) {
            $this->grapi()->domain = $api_domain;
        }
    }

    /**
     * @return array
     */
    public function getShops()
    {
        $cachedValue = $this->cache->load(self::GET_SHOPS);
        if (false !== $cachedValue) {
            return $cachedValue;
        }

        $shops = array();
        $page = 1;
        $perPage = 100;

        do {
            $apiResponse = $this->grapi()->get_shops(
                array('page' => $page, 'perPage' => $perPage)
            );
            if (isset($apiResponse->codeDescription)) {
                $apiResponse = array();
            }

            $shops = array_merge($shops, (array)$apiResponse);

            $page++;
        } while (count((array)$apiResponse) === $perPage);

        $this->cache->save($shops, self::GET_SHOPS);

        return $shops;
    }

    /**
     * Create new shop.
     *
     * @param string $name
     *
     * @return object
     */
    public function addShop($name)
    {
        $locale = substr(Mage::app()->getLocale()->getDefaultLocale(), 0, 2);
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $this->cache->remove(self::GET_SHOPS);

        return $this->grapi()->add_shop($name, $locale, $currency);
    }

    public function addCart($shopId, $params)
    {
        return $this->grapi()->add_new_cart($shopId, $params);
    }

    public function updateCart($shopId, $cartId, $params)
    {
        return $this->grapi()->update_cart($shopId, $cartId, $params);
    }

    public function deleteCart($shopId, $cartId)
    {
        return $this->grapi()->delete_cart($shopId, $cartId);
    }

    /**
     * @param string $shopId
     * @param string $productId
     *
     * @return array
     * @throws Exception
     */
    public function getProductById($shopId, $productId)
    {
        $cacheKey = md5(self::PRODUCT_CACHE_KEY . $shopId . $productId);
        $product = $this->cache->load($cacheKey);

        if (empty($product)) {
            $product = (array) $this->grapi()->get_product_by_id($shopId, $productId);
            $this->cache->save($product, $cacheKey);
        }

        return $product;
    }

    public function addProduct($shopId, $params)
    {
        return $this->grapi()->add_product($shopId, $params);
    }

    public function createOrder($shopId, $params)
    {
        return $this->grapi()->create_order($shopId, $params);
    }

    public function updateOrder($shopId, $orderId, $params)
    {
        return $this->grapi()->update_order($shopId, $orderId, $params);
    }

    /**
     * @param string $shopId
     *
     * @return bool
     */
    public function deleteShop($shopId)
    {
        $response = $this->grapi()->delete_shop($shopId);
        if (false === $response || !empty($response->codeDescription)) {
            return false;
        } else {
            $this->cache->remove(self::GET_SHOPS);

            return true;
        }
    }

    /**
     * @return mixed
     */
    public function getFromFields()
    {
        $cachedValue = $this->cache->load(self::GET_FROM_FIELDS_CACHE_KEY);
        if (false !== $cachedValue) {
            return $cachedValue;
        }

        $apiResponse = $this->grapi()->get_account_from_fields();
        $this->cache->save($apiResponse, self::GET_FROM_FIELDS_CACHE_KEY);

        return $apiResponse;
    }

    public function getSubscriptionConfirmationsSubject($code)
    {
        $cachedValue = $this->cache->load(self::GET_CONFIRMATIONS_SUBJECT);
        if (false !== $cachedValue) {
            return $cachedValue;
        }

        $apiResponse = $this->grapi()->get_subscription_confirmations_subject(
            $code
        );
        $this->cache->save($apiResponse, self::GET_CONFIRMATIONS_SUBJECT);

        return $apiResponse;
    }


    public function getSubscriptionConfirmationsBody($code)
    {
        $cachedValue = $this->cache->load(self::GET_CONFIRMATIONS_BODY);
        if (false !== $cachedValue) {
            return $cachedValue;
        }

        $apiResponse = $this->grapi()->get_subscription_confirmations_body(
            $code
        );
        $this->cache->save($apiResponse, self::GET_CONFIRMATIONS_BODY);

        return $apiResponse;
    }

    public function addCustomField($name)
    {
        $custom = $this->grapi()->add_custom_field(
            array(
                'name'   => $name,
                'type'   => "text",
                'hidden' => "false",
                'values' => array(),
            )
        );

        return $custom;
    }

    /**
     * Merges magento and getresponse custom fields.
     *
     * @param array $userCustoms    - magento custom fields
     * @param array $grCustomFields - getresponse custom fields
     *
     * @return array - merged custom fields
     */
    private function setExportCustoms($userCustoms, $grCustomFields)
    {
        $customsHashMap = array();

        if (empty($userCustoms)) {
            return $customsHashMap;
        }

        foreach ($userCustoms as $name => $value) {

            foreach ($grCustomFields as $grCustomName => $grCustomId) {
                if ($grCustomName === $name) {

                    $customsHashMap[] = array(
                        'customFieldId' => $grCustomId,
                        'value'         => is_array($value) ? $value : array($value)
                    );
                    break;
                }
            }

        }

        return $customsHashMap;
    }

    /**
     * Creates or updates GR user.
     *
     * @param string $campaign
     * @param string $name
     * @param string $email
     * @param string $cycleDay
     * @param array  $userCustoms
     * @param array  $grCustomFields
     *
     * @return int
     */
    public function upsertContact($campaign, $name, $email, $cycleDay = '',
        $userCustoms = array(), $grCustomFields = array()
    ) {
        $params = array(
            'email'     => $email,
            'campaign'  => array('campaignId' => $campaign),
            'ipAddress' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        );

        $trimName = trim($name);
        if (!empty($trimName)) {
            $params['name'] = trim($name);
        }

        if (is_numeric($cycleDay) && $cycleDay >= 0) {
            $params['dayOfCycle'] = $cycleDay;
        }

        $contact = $this->getContact($email, $campaign);

        // If contact already exists in gr account.
        if ($contact == !null && isset($contact->contactId)) {
            if (!empty($contact->customFieldValues) || !empty($userCustoms)) {
                $params['customFieldValues'] = $this->mergeUserCustoms(
                    $contact->customFieldValues, $userCustoms
                );
            }
            $response = $this->grapi()->update_contact(
                $contact->contactId, $params
            );
            if (isset($response->codeDescription)) {
                return self::CONTACT_ERROR;
            }

            return self::CONTACT_UPDATED;
        } else {
            $userCustoms['origin'] = self::ORIGIN_NAME;
            if (empty($grCustomFields)) {
                $params['customFieldValues'] = $this->setCustoms($userCustoms);
            } else {
                $params['customFieldValues'] = $this->setExportCustoms(
                    $userCustoms, $grCustomFields
                );
            }

            $response = $this->grapi()->add_contact($params);
            if (isset($response->codeDescription)) {
                return self::CONTACT_ERROR;
            }

            return self::CONTACT_CREATED;
        }
    }
}