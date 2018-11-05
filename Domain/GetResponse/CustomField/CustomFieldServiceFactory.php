<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\CustomField\CustomFieldService as GrCustomFieldService;

/**
 * Class CustomFieldServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Contact
 */
class CustomFieldServiceFactory
{
    /** @var Repository */
    private $magentoRepository;

    /** @var ShareCodeRepository */
    private $shareCodeRepository;

    /**
     * @param Repository $magentoRepository
     * @param ShareCodeRepository $shareCodeRepository
     */
    public function __construct(Repository $magentoRepository, ShareCodeRepository $shareCodeRepository)
    {
        $this->magentoRepository = $magentoRepository;
        $this->shareCodeRepository = $shareCodeRepository;
    }

    /**
     * @return GrCustomFieldService
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     */
    public function create()
    {
        $settings = ConnectionSettingsFactory::createFromArray($this->magentoRepository->getConnectionSettings());
        $getResponseApi = GetresponseApiClientFactory::createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain(),
            $this->shareCodeRepository,
            $this->magentoRepository->getGetResponsePluginVersion()
        );

        return new GrCustomFieldService($getResponseApi);
    }

}