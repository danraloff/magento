<?php

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFields;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\Authorization\ApiTypeException;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubscribeFromRegister
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeFromRegister implements ObserverInterface
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /** @var Repository */
    private $repository;

    /** @var ContactService */
    private $contactService;

    /** @var SubscribeViaRegistrationService */
    private $subscribeViaRegistrationService;

    /** @var ContactCustomFields */
    private $contactCustomFields;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param ContactService $contactService
     * @param SubscribeViaRegistrationService $subscribeViaRegistrationService
     * @param ContactCustomFields $contactCustomFields
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFields $contactCustomFields
    ) {
        $this->_objectManager = $objectManager;
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->contactCustomFields = $contactCustomFields;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $registrationSettings = $this->subscribeViaRegistrationService->getSettings();

        if (!$registrationSettings->isEnabled()) {
            return $this;
        }

        $customerData = $observer->getEvent()->getCustomer();
        $subscriber = $this->repository->loadSubscriberByEmail($customerData->getEmail());

        if ($subscriber->isSubscribed()) {

            /** @var Customer $customer */
            $customer = $this->repository->loadCustomer($customerData->getId());

            $contactCustomFieldsCollection = $this->contactCustomFields->getFromCustomer(
                $customer,
                $this->subscribeViaRegistrationService->getCustomFieldMappingSettings(),
                $registrationSettings->isUpdateCustomFieldsEnalbed()
            );

            try {
                $this->contactService->addContact(
                    $customerData->getEmail(),
                    $customerData->getFirstname(),
                    $customerData->getLastname(),
                    $registrationSettings->getCampaignId(),
                    $registrationSettings->getCycleDay(),
                    $contactCustomFieldsCollection,
                    $registrationSettings->isUpdateCustomFieldsEnalbed()
                );
            } catch (GetresponseApiException $e) {
            } catch (ConnectionSettingsException $e) {
            } catch (ApiTypeException $e) {
            }
        }

        return $this;
    }
}
