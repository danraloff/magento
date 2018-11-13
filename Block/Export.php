<?php

namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\Shop\ShopsCollection;
use GrShareCode\Shop\ShopService;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Export
 * @package GetResponse\GetResponseIntegration\Block
 */
class Export extends Template
{
    /** @var GetresponseApiClient */
    private $repositoryFactory;

    /** @var Getresponse */
    private $getResponseBlock;

    /** @var CustomFieldService */
    private $customFieldService;

    /** @var CustomFieldsMappingService */
    private $customFieldsMappingService;

    /**
     * @param Context $context
     * @param RepositoryFactory $repositoryFactory
     * @param Getresponse $getResponseBlock
     * @param CustomFieldService $customFieldService
     * @param CustomFieldsMappingService $customFieldsMappingService
     */
    public function __construct(
        Context $context,
        RepositoryFactory $repositoryFactory,
        Getresponse $getResponseBlock,
        CustomFieldService $customFieldService,
        CustomFieldsMappingService $customFieldsMappingService
    ) {
        parent::__construct($context);
        $this->repositoryFactory = $repositoryFactory;
        $this->getResponseBlock = $getResponseBlock;
        $this->customFieldService = $customFieldService;
        $this->customFieldsMappingService = $customFieldsMappingService;
    }

    /**
     * @return SubscribeViaRegistration
     */
    public function getExportSettings()
    {
        return $this->getResponseBlock->getRegistrationSettings();
    }

    /**
     * @return CustomFieldsMappingCollection
     */
    public function getCustomFieldsMapping()
    {
        return $this->getResponseBlock->getCustomFieldsMappingForRegistration();
    }

    /**
     * @return ContactListCollection
     * @throws RepositoryException
     * @throws GetresponseApiException
     */
    public function getCampaigns()
    {
        return (new ContactListService($this->repositoryFactory->createGetResponseApiClient()))->getAllContactLists();
    }

    /**
     * @return ShopsCollection
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    public function getShops()
    {
        return (new ShopService($this->repositoryFactory->createGetResponseApiClient()))->getAllShops();
    }

    /**
     * @return array
     */
    public function getAutoRespondersForFrontend()
    {
        return $this->getResponseBlock->getAutoRespondersForFrontend();
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
