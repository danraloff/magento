<?php

namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account as GrAccount;
use GetResponse\GetResponseIntegration\Domain\GetResponse\AccountFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\ContactList\Autoresponder;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\GetresponseApiException;

/**
 * Class Getresponse
 * @package GetResponse\GetResponseIntegration\Block
 */
class Getresponse
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /**
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(
        Repository $repository,
        RepositoryFactory $repositoryFactory
    ) {
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @return array
     */
    public function getAutoResponders()
    {
        try {
            $result = [];
            $grApiClient = $this->repositoryFactory->createGetResponseApiClient();

            $service = new ContactListService($grApiClient);
            $responders = $service->getAutoresponders();

            /** @var Autoresponder $responder */
            foreach ($responders as $responder) {
                $result[$responder->getCampaignId()][$responder->getId()] = [
                    'name' => $responder->getName(),
                    'subject' => $responder->getSubject(),
                    'dayOfCycle' => $responder->getCycleDay()
                ];
            }

            return $result;
        } catch (RepositoryException $e) {
            return [];
        } catch (GetresponseApiException $e) {
            return [];
        }
    }

    /**
     * @return array
     */
    public function getAutoRespondersForFrontend()
    {
        $responders = $this->getAutoResponders();
        if (empty($responders)) {
            return [];
        }
        return $responders;
    }

    /**
     * @return SubscribeViaRegistration
     */
    public function getRegistrationSettings()
    {
        return SubscribeViaRegistrationFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );
    }

    /**
     * @return NewsletterSettings
     */
    public function getNewsletterSettings()
    {
        return NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings()
        );
    }

    /**
     * @return CustomFieldsMappingCollection
     */
    public function getCustomFieldsMappingForRegistration()
    {
        return CustomFieldsMappingCollection::createFromRepository(
            $this->repository->getCustomFieldsMappingForRegistration()
        );
    }

    /**
     * @return GrAccount
     */
    public function getAccountInfo()
    {
        return AccountFactory::createFromArray($this->repository->getAccountInfo());
    }

}
