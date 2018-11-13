<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Api;

use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Api\Authorization\ApiTypeException;
use GrShareCode\Api\GetresponseApiClient;

/**
 * Class ApiFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Api
 */
class ApiFactory
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
     * @return GetresponseApiClient
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     */
    public function create()
    {
        $settings = ConnectionSettingsFactory::createFromArray($this->magentoRepository->getConnectionSettings());

        return GetresponseApiClientFactory::createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain(),
            $this->shareCodeRepository,
            $this->magentoRepository->getGetResponsePluginVersion()
        );
    }
}