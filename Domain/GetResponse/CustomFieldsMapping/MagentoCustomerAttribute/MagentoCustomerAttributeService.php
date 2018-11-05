<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Customer;

/**
 * Class MagentoCustomerAttributeService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute
 */
class MagentoCustomerAttributeService
{
    /**
     * @param Customer $customer
     * @param string $attributeCode
     * @return mixed
     */
    public function getCustomerAttributeValueByCode(Customer $customer, $attributeCode)
    {
        /** @var Attribute $customerAttribute */
        $customerAttribute = $customer->getAttribute($attributeCode);

        return $customerAttribute->getFrontend()->getValue($customer);
    }
}