<?php
namespace Tonder\Payment\Gateway\Command\Net;

use Tonder\Payment\Gateway\Helper\ResponseReader;
use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment;

/**
 * Class UpdateOrderCommand
 */
class UpdateOrderCommand implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ResponseReader
     */
    private $responseReader;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * UpdateOrderCommand constructor.
     * @param ConfigInterface $config
     * @param OrderRepositoryInterface $orderRepository
     * @param ResponseReader $responseReader
     * @param OrderSender $orderSender
     * @param Session $session
     */
    public function __construct(
        ConfigInterface $config,
        OrderRepositoryInterface $orderRepository,
        ResponseReader $responseReader,
        OrderSender $orderSender,
        Session $session
    ) {
        $this->config = $config;
        $this->responseReader = $responseReader;
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->checkoutSession = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $response = $this->responseReader->readResponse($commandSubject);
        if (array_key_exists('trans_name', $response)) {
            $transactionType = $response['trans_name'];
        } else {
            $transactionType = $response['txn_type'];
        }
        switch ($transactionType) {
            case 'purchase':
            case 'cavv_purchase':
                $payment->capture();
                break;
            case 'preauth':
            case 'cavv_preauth':
                $payment->authorize(
                    false,
                    $paymentDO->getOrder()->getGrandTotalAmount()
                );
                break;
        }
        $order = $payment->getOrder();
        $this->orderRepository->save($order);
        if ($order->getCanSendNewEmailFlag()) {
            $this->orderSender->send($order);
        }
        $this->checkoutSession->setLastQuoteId($order->getQuoteId())
            ->setLastSuccessQuoteId($order->getQuoteId())
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());
    }
}
