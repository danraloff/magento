<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Api\Authorization\ApiTypeException;
use GrShareCode\Export\ExportContactService;
use GrShareCode\Export\ExportContactServiceFactory;

/**
 * Class ExportServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand
 */
class ExportServiceFactory
{
    /** @var ApiFactory */
    private $apiFactory;

    /** @var ShareCodeRepository */
    private $shareCodeRepository;

    /**
     * @param ApiFactory $apiFactory
     * @param ShareCodeRepository $shareCodeRepository
     */
    public function __construct(ApiFactory $apiFactory, ShareCodeRepository $shareCodeRepository)
    {
        $this->apiFactory = $apiFactory;
        $this->shareCodeRepository = $shareCodeRepository;
    }

    /**
     * @return ExportContactService
     * @throws ConnectionSettingsException
     * @throws ApiTypeException
     */
    public function create()
    {
        return ExportContactServiceFactory::create(
            $this->apiFactory->create(),
            $this->shareCodeRepository,
            Config::ORIGIN_NAME
        );
    }


}