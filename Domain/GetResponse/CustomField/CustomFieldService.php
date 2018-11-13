<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField;

use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\CustomField\CustomFieldCollection;

/**
 * Class CustomFieldService
 * @package Domain\GetResponse\CustomField
 */
class CustomFieldService
{
    /** @var CustomFieldServiceFactory */
    private $customFieldServiceFactory;

    /**
     * @param CustomFieldServiceFactory $customFieldServiceFactory
     */
    public function __construct(CustomFieldServiceFactory $customFieldServiceFactory)
    {
        $this->customFieldServiceFactory = $customFieldServiceFactory;
    }

    /**
     * @return CustomFieldCollection
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    public function getCustomFields()
    {
        $grCustomFieldService = $this->customFieldServiceFactory->create();

        return $grCustomFieldService->getCustomFieldsForMapping();
    }

}