<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFields;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\GetresponseApiException;
use Magento\Customer\Model\Customer;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order;

/**
 * Class ExportOnDemandService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand
 */
class ExportOnDemandService
{
    /** @var ContactService */
    private $contactService;

    /** @var ContactCustomFields */
    private $contactCustomFields;

    /** @var Repository */
    private $repository;

    /** @var OrderService */
    private $orderService;

    /** @var AddOrderCommandFactory */
    private $addOrderCommandFactory;

    public function __construct(
        Repository $repository,
        ContactService $contactService,
        ContactCustomFields $contactCustomFields,
        OrderService $orderService,
        AddOrderCommandFactory $addOrderCommandFactory
    ) {
        $this->contactService = $contactService;
        $this->contactCustomFields = $contactCustomFields;
        $this->repository = $repository;
        $this->orderService = $orderService;
        $this->addOrderCommandFactory = $addOrderCommandFactory;
    }

    /**
     * @param Subscriber $subscriber
     * @param ExportOnDemand $exportOnDemand
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    public function export(Subscriber $subscriber, ExportOnDemand $exportOnDemand)
    {
        if (!$this->subscriberIsAlsoCustomer($subscriber)) {
            $this->sendSubscriberToGetResponse($subscriber, $exportOnDemand);

            return;
        }

        $customer = $this->repository->loadCustomer($subscriber->getCustomerId());

        $this->sendCustomerToGetResponse($customer, $exportOnDemand);
        $this->sendCustomerOrdersToGetResponse($customer, $exportOnDemand);
    }

    /**
     * @param $subscriber
     * @return bool
     */
    private function subscriberIsAlsoCustomer(Subscriber $subscriber)
    {
        return 0 !== (int)$subscriber->getCustomerId();
    }

    /**
     * @param Subscriber $subscriber
     * @param ExportOnDemand $exportOnDemand
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    private function sendSubscriberToGetResponse(Subscriber $subscriber, ExportOnDemand $exportOnDemand)
    {
        $this->contactService->upsertContact(
            $subscriber['subscriber_email'],
            '',
            '',
            $exportOnDemand->getContactListId(),
            $exportOnDemand->getDayOfCycle(),
            $this->contactCustomFields->getForSubscriber()
        );
    }

    /**
     * @param Customer $customer
     * @param ExportOnDemand $exportOnDemand
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    private function sendCustomerToGetResponse(Customer $customer, ExportOnDemand $exportOnDemand)
    {
        $contactCustomFieldCollection = $this->contactCustomFields->getFromCustomer(
            $customer,
            $exportOnDemand->getCustomFieldsMappingCollection(),
            $exportOnDemand->isUpdateContactCustomFieldEnabled()
        );

        $this->contactService->upsertContact(
            $customer->getEmail(),
            $customer->getFirstname(),
            $customer->getLastname(),
            $exportOnDemand->getContactListId(),
            $exportOnDemand->getDayOfCycle(),
            $contactCustomFieldCollection
        );
    }

    /**
     * @param Customer $customer
     * @param ExportOnDemand $exportOnDemand
     */
    private function sendCustomerOrdersToGetResponse(Customer $customer, ExportOnDemand $exportOnDemand)
    {
        if (!$exportOnDemand->isSendEcommerceDataEnabled()) {
            return;
        }

        $orders = $this->repository->getOrderByCustomerId($customer->getId());

        /** @var Order $order */
        foreach ($orders as $order) {

            try {
                $this->orderService->exportOrder(
                    $this->addOrderCommandFactory->createForOrderService(
                        $order,
                        $exportOnDemand->getContactListId(),
                        $exportOnDemand->getShopId()
                    )
                );
            } catch (Exception $e) {
            }
        }
    }

}