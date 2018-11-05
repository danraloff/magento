<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

/**
 * Class CustomFieldMappingDto
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto
 */
class CustomFieldMappingDto
{
    /** @var string */
    private $magentoAttributeCode;

    /** @var string */
    private $getResponseCustomFieldId;

    /**
     * @param string $magentoAttributeCode
     * @param string $getResponseCustomFieldId
     */
    public function __construct($magentoAttributeCode, $getResponseCustomFieldId)
    {
        $this->magentoAttributeCode = $magentoAttributeCode;
        $this->getResponseCustomFieldId = $getResponseCustomFieldId;
    }

    /**
     * @return string
     */
    public function getMagentoAttributeCode()
    {
        return $this->magentoAttributeCode;
    }

    /**
     * @return string
     */
    public function getGetResponseCustomFieldId()
    {
        return $this->getResponseCustomFieldId;
    }


}