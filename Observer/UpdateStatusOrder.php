<?php
declare(strict_types=1);

namespace Tonder\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Tonder\Payment\Logger\Logger;

class UpdateStatusOrder implements ObserverInterface
{
    const PAYMENT_CODE = "tonder";

    const PAYMENT_APPROVED = 'payment_approved';

    /**
     * @var Logger
     */
    private $logger;

    private $order;

    public function __construct(
        Order $order,
        Logger $logger
    )
    {
        $this->order = $order;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $orderId = $observer->getEvent()->getOrder()->getId();
            $order = $this->order->load($orderId);

            if ($order->getPayment()->getMethod() === self::PAYMENT_CODE && $order->getState() === Order::STATE_PROCESSING) {
                $order->setStatus(self::PAYMENT_APPROVED);
                $order->addStatusToHistory(self::PAYMENT_APPROVED, 'Pago Aprobado por Tonder', true)->setIsCustomerNotified(true);
                $order->save();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
