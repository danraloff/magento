<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

/**
 * Class MagentoCustomerAttribute
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute
 */
class MagentoCustomerAttribute
{
    const ATTRIBUTE_CODE_EMAIL = 'email';
    const ATTRIBUTE_CODE_FIRST_NAME = 'firstname';
    const ATTRIBUTE_CODE_LAST_NAME = 'lastname';

    /** @var int */
    private $attributeCode;

    /** @var string */
    private $frontendLabel;

    /**
     * @param string $attributeCode
     * @param string $frontendLabel
     */
    public function __construct($attributeCode, $frontendLabel)
    {
        $this->attributeCode = $attributeCode;
        $this->frontendLabel = $frontendLabel;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'attributeCode' => $this->getAttributeCode(),
            'frontendlabel' => $this->getFrontendLabel()
        ];
    }

    /**
     * @return int
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @return string
     */
    public function getFrontendLabel()
    {
        return $this->frontendLabel;
    }

}