<?php

namespace Tonder\Payment\Model\Multishipping;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

class PlaceOrder implements PlaceOrderInterface
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * PlaceOrder constructor.
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(OrderManagementInterface $orderManagement)
    {
        $this->orderManagement = $orderManagement;
    }

    /**
     * @param array $orderList
     * @return array
     */
    public function place(array $orderList): array
    {
        $errorList = [];
        foreach ($orderList as $order) {
            try {
                $this->orderManagement->place($order);
            } catch (\Exception $e) {
                $incrementId = $order->getIncrementId();
                $errorList[$incrementId] = $e;
            }
        }

        return $errorList;
    }
}
