<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\GetresponseApiException;
use GrShareCode\Order\Command\AddOrderCommand;
use GrShareCode\Order\Command\EditOrderCommand;

/**
 * Class OrderService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class OrderService
{
    /** @var OrderServiceFactory */
    private $orderServiceFactory;

    /**
     * @param OrderServiceFactory $orderServiceFactory
     */
    public function __construct(OrderServiceFactory $orderServiceFactory) {
        $this->orderServiceFactory = $orderServiceFactory;
    }

    /**
     * @param AddOrderCommand $addOrderCommand
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    public function addOrder(AddOrderCommand $addOrderCommand)
    {
        $orderService = $this->orderServiceFactory->create();
        $orderService->addOrder($addOrderCommand);
    }

    /**
     * @param EditOrderCommand $editOrderCommand
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    public function updateOrder(EditOrderCommand $editOrderCommand)
    {
        $orderService = $this->orderServiceFactory->create();
        $orderService->updateOrder($editOrderCommand);
    }

}