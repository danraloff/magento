<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttribute;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;

/**
 * Class CustomFieldsMappingService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping
 */
class CustomFieldsMappingService
{
    const BLACKLISTED_ATTRIBUTE_CODES = [
        'store_id',
        'disable_auto_group_change'
    ];

    /** @var Repository */
    private $repository;

    /** @var CollectionFactory */
    private $customerAttributeCollectionFactory;

    /**
     * @param Repository $repository
     * @param CollectionFactory $customerAttributeCollectionFactory
     */
    public function __construct(
        Repository $repository,
        CollectionFactory $customerAttributeCollectionFactory
    ) {
        $this->repository = $repository;
        $this->customerAttributeCollectionFactory = $customerAttributeCollectionFactory;
    }

    public function setDefaultCustomFields()
    {
        $customFieldMappingCollection = CustomFieldsMappingCollection::createDefaults();
        $this->repository->setCustomsOnInit($customFieldMappingCollection->toArray());
    }

    /**
     * @return MagentoCustomerAttributeCollection
     */
    public function getMagentoCustomerAttributes()
    {
        $attributeCollection = new MagentoCustomerAttributeCollection();

        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach ($this->customerAttributeCollectionFactory->create() as $attribute) {

            if (null === $attribute->getFrontendLabel()) {
                continue;
            }

            if (in_array($attribute->getAttributeCode(), self::BLACKLISTED_ATTRIBUTE_CODES, true)) {
                continue;
            }

            $attributeCollection->add(
                new MagentoCustomerAttribute($attribute->getAttributeCode(), $attribute->getFrontendLabel())
            );
        }

        return $attributeCollection;
    }


}