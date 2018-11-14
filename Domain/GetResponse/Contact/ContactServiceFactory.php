<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Api\Authorization\ApiTypeException;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldCollectionFactory;
use GrShareCode\Contact\ContactFactory;
use GrShareCode\Contact\ContactPayloadFactory;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\CustomField\CustomFieldService;

/**
 * Class ContactServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Contact
 */
class ContactServiceFactory
{
    /** @var Repository */
    private $magentoRepository;

    /** @var ShareCodeRepository */
    private $shareCodeRepository;

    /** @var GetresponseApiClientFactory */
    private $apiClientFactory;

    /**
     * @param Repository $magentoRepository
     * @param ShareCodeRepository $shareCodeRepository
     * @param GetresponseApiClientFactory $apiClientFactory
     */
    public function __construct(
        Repository $magentoRepository,
        ShareCodeRepository $shareCodeRepository,
        GetresponseApiClientFactory $apiClientFactory
    ) {
        $this->magentoRepository = $magentoRepository;
        $this->shareCodeRepository = $shareCodeRepository;
        $this->apiClientFactory = $apiClientFactory;
    }

    /**
     * @return GrContactService
     * @throws ConnectionSettingsException
     * @throws ApiTypeException
     */
    public function create()
    {
        $settings = ConnectionSettingsFactory::createFromArray($this->magentoRepository->getConnectionSettings());
        $getResponseApi = $this->apiClientFactory->createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain()
        );

        return new GrContactService(
            $getResponseApi,
            new ContactPayloadFactory(),
            new ContactFactory(new ContactCustomFieldCollectionFactory()),
            new CustomFieldService($getResponseApi),
            $this->shareCodeRepository,
            Config::ORIGIN_NAME
        );
    }
}