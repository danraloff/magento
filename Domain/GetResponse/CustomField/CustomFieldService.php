<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField;

use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\CustomField\CustomFieldCollection;
use GrShareCode\CustomField\CustomFieldFilter\TextFieldCustomFieldFilter;
use GrShareCode\GetresponseApiException;

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
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    public function getCustomFields()
    {
        $grCustomFieldService = $this->customFieldServiceFactory->create();

        return $grCustomFieldService->getAllCustomFields(new TextFieldCustomFieldFilter());
    }

}