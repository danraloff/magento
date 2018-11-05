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
use GrShareCode\Api\ApiTypeException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\GetresponseApiException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Registration
 * @package GetResponse\GetResponseIntegration\Block
 */
class Registration extends Template
{
    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var Getresponse */
    private $getresponseBlock;

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
        $this->getresponseBlock = $getResponseBlock;
        $this->customFieldService = $customFieldService;
        $this->customFieldsMappingService = $customFieldsMappingService;
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
     * @throws ApiTypeException
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
