<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFields;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeService;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Contact\ContactCustomField\ContactCustomField;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;
use Magento\Customer\Model\Customer;

class ContactCustomFieldsTest extends BaseTestCase
{
    /** @var ContactCustomFields */
    private $sut;

    /** @var MagentoCustomerAttributeService|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoCustomerAttributeService;

    protected function setUp()
    {
        $this->magentoCustomerAttributeService = $this->getMockWithoutConstructing(MagentoCustomerAttributeService::class);
        $this->sut = new ContactCustomFields($this->magentoCustomerAttributeService);
    }

    /**
     * @test
     */
    public function shouldReturnEmptyContactCustomFieldsCollectionForSubscriber()
    {
        $this->assertEquals(new ContactCustomFieldsCollection(), $this->sut->getForSubscriber());
    }

    /**
     * @test
     */
    public function shouldReturnContactCustomFieldCollectionForCustomer()
    {
        $getResponseCustomId1 = 'grCustomId1';
        $getResponseCustomId2 = 'grCustomId2';
        $getResponseCustomId3 = 'grCustomId3';

        $attributeCode1 = 'attrCode1';
        $attributeCode2 = 'attrCode2';
        $attributeCode3 = 'attrCode3';

        $customerAttributeValue1 = 'AttributeValue1';
        $customerAttributeValue2 = 'AttributeValue2';
        $customerAttributeValue3 = 'AttributeValue3';

        /** @var Customer|\PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMockWithoutConstructing(Customer::class);
        $customFieldsMappingCollection = CustomFieldsMappingCollection::createDefaults();
        $customFieldsMappingCollection->add(
            new CustomFieldsMapping($getResponseCustomId1, $attributeCode1, false, '')
        );
        $customFieldsMappingCollection->add(
            new CustomFieldsMapping($getResponseCustomId2, $attributeCode2, false, '')
        );
        $customFieldsMappingCollection->add(
            new CustomFieldsMapping($getResponseCustomId3, $attributeCode3, false, '')
        );
        $this->magentoCustomerAttributeService
            ->expects(self::exactly(3))
            ->method('getCustomerAttributeValueByCode')
            ->withConsecutive(
                [$customer, $attributeCode1],
                [$customer, $attributeCode2],
                [$customer, $attributeCode3]
             )
            ->willReturn($customerAttributeValue1, $customerAttributeValue2, $customerAttributeValue3);

        $isCustomFieldUpdateEnabled = true;

        $expectedContactCustomFieldsCollection = new ContactCustomFieldsCollection();
        $expectedContactCustomFieldsCollection->add(new ContactCustomField($getResponseCustomId1, [$customerAttributeValue1]));
        $expectedContactCustomFieldsCollection->add(new ContactCustomField($getResponseCustomId2, [$customerAttributeValue2]));
        $expectedContactCustomFieldsCollection->add(new ContactCustomField($getResponseCustomId3, [$customerAttributeValue3]));

        $actualContactCustomFieldsCollection = $this->sut->getFromCustomer($customer, $customFieldsMappingCollection, $isCustomFieldUpdateEnabled);

        $this->assertEquals($expectedContactCustomFieldsCollection, $actualContactCustomFieldsCollection);
    }

    /**
     * @test
     */
    public function shouldReturnEmptyContactCustomFieldCollectionForEmptyCustomFieldMapping()
    {
        /** @var Customer|\PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMockWithoutConstructing(Customer::class);

        $customFieldsMappingCollection = CustomFieldsMappingCollection::createDefaults();
        $isCustomFieldUpdateEnabled = true;

        $expectedContactCustomFieldsCollection = new ContactCustomFieldsCollection();
        $actualContactCustomFieldsCollection = $this->sut->getFromCustomer($customer, $customFieldsMappingCollection, $isCustomFieldUpdateEnabled);

        $this->assertEquals($expectedContactCustomFieldsCollection, $actualContactCustomFieldsCollection);
    }

    /**
     * @test
     */
    public function shouldReturnContactCustomFieldCollectionForEmptyAttributesValue()
    {
        $getResponseCustomId1 = 'grCustomId1';
        $getResponseCustomId2 = 'grCustomId2';
        $getResponseCustomId3 = 'grCustomId3';

        $attributeCode1 = 'attrCode1';
        $attributeCode2 = 'attrCode2';
        $attributeCode3 = 'attrCode3';

        $customerAttributeValue1 = null;
        $customerAttributeValue2 = 'AttributeValue2';
        $customerAttributeValue3 = null;

        /** @var Customer|\PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMockWithoutConstructing(Customer::class);
        $customFieldsMappingCollection = CustomFieldsMappingCollection::createDefaults();
        $customFieldsMappingCollection->add(
            new CustomFieldsMapping($getResponseCustomId1, $attributeCode1, false, '')
        );
        $customFieldsMappingCollection->add(
            new CustomFieldsMapping($getResponseCustomId2, $attributeCode2, false, '')
        );
        $customFieldsMappingCollection->add(
            new CustomFieldsMapping($getResponseCustomId3, $attributeCode3, false, '')
        );

        $this->magentoCustomerAttributeService
            ->expects(self::exactly(3))
            ->method('getCustomerAttributeValueByCode')
            ->withConsecutive(
                [$customer, $attributeCode1],
                [$customer, $attributeCode2],
                [$customer, $attributeCode3]
            )
            ->willReturn($customerAttributeValue1, $customerAttributeValue2, $customerAttributeValue3);

        $isCustomFieldUpdateEnabled = true;

        $expectedContactCustomFieldsCollection = new ContactCustomFieldsCollection();
        $expectedContactCustomFieldsCollection->add(new ContactCustomField($getResponseCustomId2, [$customerAttributeValue2]));

        $actualContactCustomFieldsCollection = $this->sut->getFromCustomer($customer, $customFieldsMappingCollection, $isCustomFieldUpdateEnabled);

        $this->assertEquals($expectedContactCustomFieldsCollection, $actualContactCustomFieldsCollection);
    }

}
