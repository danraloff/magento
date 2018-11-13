<?php

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFields;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\Authorization\ApiTypeException;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class SubscribeFromNewsletter
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeFromNewsletter implements ObserverInterface
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var Repository */
    private $repository;

    /** @var ContactService */
    private $contactService;

    /** @var ContactCustomFields */
    private $contactCustomFields;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param ContactCustomFields $contactCustomFields
     * @param ContactService $contactService
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        ContactCustomFields $contactCustomFields,
        ContactService $contactService
    ) {
        $this->objectManager = $objectManager;
        $this->repository = $repository;
        $this->contactCustomFields = $contactCustomFields;
        $this->contactService = $contactService;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $newsletterSettings = NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings()
        );

        if (!$newsletterSettings->isEnabled()) {
            return $this;
        }

        try {

            /** @var Subscriber $subscriber */
            $subscriber = $observer->getEvent()->getSubscriber();

            if ($subscriber->getCustomerId() > 0) {
                return $this;
            }

            $email = $subscriber->getEmail();

            if (empty($email)) {
                return $this;
            }

            $this->contactService->addContact(
                $email,
                '',
                '',
                $newsletterSettings->getCampaignId(),
                $newsletterSettings->getCycleDay(),
                $this->contactCustomFields->getForSubscriber(),
                false
            );
        } catch (RepositoryException $e) {
        } catch (ApiTypeException $e) {
        } catch (GetresponseApiException $e) {
        } catch (ConnectionSettingsException $e) {
        }
    }
}
