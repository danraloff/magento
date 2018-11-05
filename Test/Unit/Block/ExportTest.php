<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Export;
use GetResponse\GetResponseIntegration\Block\Export as ExportBlock;
use GetResponse\GetResponseIntegration\Block\Getresponse;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\GetresponseApiClient;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ExportTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class ExportTest extends BaseTestCase
{
    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var RepositoryFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var ExportBlock */
    private $exportBlock;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var GetresponseApiClient|\PHPUnit_Framework_MockObject_MockObject */
    private $grApiClient;

    /** @var CustomFieldService|\PHPUnit_Framework_MockObject_MockObject */
    private $customFieldsService;

    /** @var CustomFieldsMappingService|\PHPUnit_Framework_MockObject_MockObject */
    private $customFieldsMappingService;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->repositoryFactory = $this->getMockWithoutConstructing(RepositoryFactory::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);

        $this->customFieldsService = $this->getMockWithoutConstructing(CustomFieldService::class);
        $this->customFieldsMappingService = $this->getMockWithoutConstructing(CustomFieldsMappingService::class);

        $getresponseBlock = new Getresponse($this->repository, $this->repositoryFactory);
        $this->exportBlock = new ExportBlock(
            $this->context,
            $this->repositoryFactory,
            $getresponseBlock,
            $this->customFieldsService,
            $this->customFieldsMappingService
        );
    }

    /**
     * @test
     * @param array $settings
     * @param SubscribeViaRegistration $expectedExportSettings
     *
     * @dataProvider shouldReturnExportSettingsProvider
     */
    public function shouldReturnExportSettings(array $settings, SubscribeViaRegistration $expectedExportSettings)
    {
        $this->repository->expects($this->atLeastOnce())->method('getRegistrationSettings')->willReturn($settings);
        $exportSettings = $this->exportBlock->getExportSettings();

        self::assertEquals($exportSettings, $expectedExportSettings);
    }

    /**
     * @return array
     */
    public function shouldReturnExportSettingsProvider()
    {
        return [
            [[], new SubscribeViaRegistration(0, 0, '', 0, '')],
            [
                [
                    'status' => 1,
                    'customFieldsStatus' => 0,
                    'campaignId' => '1v4',
                    'cycleDay' => 6,
                    'autoresponderId' => 'x3'
                ],
                new SubscribeViaRegistration(1, 0, '1v4', 6, 'x3')
            ]
        ];
    }

    /**
     * @test
     *
     * @param array $rawCustoms
     * @param CustomFieldsMapping $expectedFirstCustom
     * @dataProvider shouldReturnCustomsProvider
     */
    public function shouldReturnCustoms(array $rawCustoms, CustomFieldsMapping $expectedFirstCustom)
    {
        $this->repository->expects($this->once())->method('getCustomFieldsMappingForRegistration')->willReturn($rawCustoms);

        $customFieldMappingCollection = $this->exportBlock->getCustomFieldsMapping();
        self::assertInstanceOf(CustomFieldsMappingCollection::class, $customFieldMappingCollection);

        if (count($customFieldMappingCollection->getIterator())) {

            $custom = $customFieldMappingCollection->getIterator()[0];
            self::assertInstanceOf(CustomFieldsMapping::class, $custom);
            self::assertEquals($expectedFirstCustom->getMagentoAttributeCode(), $custom->getMagentoAttributeCode());
            self::assertEquals($expectedFirstCustom->getGetResponseCustomId(), $custom->getGetResponseCustomId());
            self::assertEquals($expectedFirstCustom->isDefault(), $custom->isDefault());
            self::assertEquals($expectedFirstCustom->getGetResponseDefaultLabel(), $custom->getGetResponseDefaultLabel());
        }
    }

    /**
     * @return array
     */
    public function shouldReturnCustomsProvider()
    {
        $getResponseCustomId = 'getResponseCustomId';
        $magentoAttributeCode = 'magentoAttributeCode';
        $isDefault = false;
        $getResponseDefaultLabel = '';

        $rawCustomField = [
            'getResponseCustomId' => $getResponseCustomId,
            'magentoAttributeCode' => $magentoAttributeCode,
            'getResponseDefaultLabel' => $getResponseDefaultLabel,
            'default' => $isDefault
        ];

        return [
            [
                [],
                new CustomFieldsMapping(0, '', '', '')
            ],
            [
                [$rawCustomField],
                new CustomFieldsMapping(
                    $getResponseCustomId,
                    $magentoAttributeCode,
                    $isDefault,
                    $getResponseDefaultLabel
                )
            ]
        ];
    }
}
