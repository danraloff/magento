<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFields;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Export\Command\ExportContactCommand;
use GrShareCode\Order\OrderCollection;
use Magento\Customer\Model\Customer;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order;

/**
 * Class ExportOnDemandService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand
 */
class ExportOnDemandService
{
    /** @var ContactCustomFields */
    private $contactCustomFields;

    /** @var Repository */
    private $repository;

    /** @var OrderFactory */
    private $orderFactory;

    /** @var ExportServiceFactory */
    private $exportServiceFactory;

    public function __construct(
        Repository $repository,
        ContactCustomFields $contactCustomFields,
        OrderFactory $orderFactory,
        ExportServiceFactory $exportServiceFactory
    ) {
        $this->repository = $repository;
        $this->contactCustomFields = $contactCustomFields;
        $this->orderFactory = $orderFactory;
        $this->exportServiceFactory = $exportServiceFactory;
    }

    /**
     * @param Subscriber $subscriber
     * @param ExportOnDemand $exportOnDemand
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    public function export(Subscriber $subscriber, ExportOnDemand $exportOnDemand)
    {
        $grExportService = $this->exportServiceFactory->create();

        if (!$this->subscriberIsAlsoCustomer($subscriber)) {

            $grExportService->exportContact(
                $this->createExportCommandForSubscriber($subscriber, $exportOnDemand)
            );

            return;
        }

        $customer = $this->repository->loadCustomer($subscriber->getCustomerId());

        $grExportService->exportContact(
            $this->createExportCommandForCustomer($customer, $exportOnDemand)
        );

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
     * @return ExportContactCommand
     */
    private function createExportCommandForSubscriber(Subscriber $subscriber, ExportOnDemand $exportOnDemand)
    {
        $exportSettings = ExportSettingsFactory::createFromExportOnDemand($exportOnDemand);

        return new ExportContactCommand(
            $subscriber['subscriber_email'],
            '',
            $exportSettings,
            $this->contactCustomFields->getForSubscriber(),
            new OrderCollection()
        );
    }

    /**
     * @param Customer $customer
     * @param ExportOnDemand $exportOnDemand
     * @return ExportContactCommand
     */
    private function createExportCommandForCustomer($customer, ExportOnDemand $exportOnDemand)
    {
        $exportSettings = ExportSettingsFactory::createFromExportOnDemand($exportOnDemand);

        $contactCustomFieldCollection = $this->contactCustomFields->getFromCustomer(
            $customer,
            $exportOnDemand->getCustomFieldsMappingCollection(),
            $exportOnDemand->isUpdateContactCustomFieldEnabled()
        );

        return new ExportContactCommand(
            $customer->getEmail(),
            trim($customer->getFirstname() . ' ' . $customer->getLastname()),
            $exportSettings,
            $contactCustomFieldCollection,
            $this->getCustomerOrderCollection($customer, $exportOnDemand)
        );
    }

    /**
     * @param Customer $customer
     * @param ExportOnDemand $exportOnDemand
     * @return OrderCollection
     */
    private function getCustomerOrderCollection(Customer $customer, ExportOnDemand $exportOnDemand)
    {
        $orderCollection = new OrderCollection();

        if (!$exportOnDemand->isSendEcommerceDataEnabled()) {
            return $orderCollection;
        }

        $orders = $this->repository->getOrderByCustomerId($customer->getId());

        /** @var Order $order */
        foreach ($orders as $order) {

            $orderCollection->add(
                $this->orderFactory->fromMagentoOrder($order)
            );
        }

        return $orderCollection;
    }

}