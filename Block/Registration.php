<?php
namespace GetResponse\GetResponseIntegration\Block;

use Ebizmarts\MailChimp\Controller\Adminhtml\Errors\Getresponse;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Registration
 * @package GetResponse\GetResponseIntegration\Block
 */
class Registration extends Template
{
    /** @var GetresponseApiClientFactory */
    private $apiClientFactory;

    /** @var Getresponse */
    private $getresponseBlock;

    /** @var CustomFieldService */
    private $customFieldService;

    /** @var CustomFieldsMappingService */
    private $customFieldsMappingService;

    /**
     * @param Context $context
     * @param GetresponseApiClientFactory $apiClientFactory
     * @param Getresponse $getResponseBlock
     * @param CustomFieldService $customFieldService
     * @param CustomFieldsMappingService $customFieldsMappingService
     */
    public function __construct(
        Context $context,
        GetresponseApiClientFactory $apiClientFactory,
        Getresponse $getResponseBlock,
        CustomFieldService $customFieldService,
        CustomFieldsMappingService $customFieldsMappingService
    ) {
        parent::__construct($context);
        $this->apiClientFactory = $apiClientFactory;
        $this->getresponseBlock = $getResponseBlock;
        $this->customFieldService = $customFieldService;
        $this->customFieldsMappingService = $customFieldsMappingService;
    }

    /**
     * @return ContactListCollection
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    public function getCampaigns()
    {
        return (new ContactListService($this->apiClientFactory->createGetResponseApiClient()))->getAllContactLists();
    }

    /**
     * @return array
     */
    public function getAutoResponders()
    {
        return $this->getresponseBlock->getAutoResponders();
    }

    /**
     * @return array
     */
    public function getAutoRespondersForFrontend()
    {
        return $this->getresponseBlock->getAutoRespondersForFrontend();
    }

    /**
     * @return CustomFieldsMappingCollection
     */
    public function getCustomFieldsMapping()
    {
        return $this->getresponseBlock->getCustomFieldsMappingForRegistration();
    }

    /**
     * @return SubscribeViaRegistration
     */
    public function getRegistrationSettings()
    {
        return $this->getresponseBlock->getRegistrationSettings();
    }

    /**
     * @return array
     * @throws GetresponseApiException
     * @throws ConnectionSettingsException
     */
    public function getCustomFieldsFromGetResponse()
    {
        $result = [];

        foreach ($this->customFieldService->getCustomFields() as $customField) {
            $result[] = [
                'id' => $customField->getId(),
                'name' => $customField->getName(),
            ];
        }

        return $result;
    }


    /**
     * @return MagentoCustomerAttributeCollection
     */
    public function getMagentoCustomerAttributes()
    {
        return $this->customFieldsMappingService->getMagentoCustomerAttributes();
    }
}
