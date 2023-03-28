<?php
namespace Tonder\Payment\Gateway\Response;

use Magento\Checkout\Helper\Data;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CompletePaymentHandler
 * @package Tonder\Payment\Gateway\Response
 */
class ThreeDSecureHandler implements HandlerInterface
{
    const MESSAGE = "Message";
    const CAVV_RESULT_CODE = 'CavvResultCode';
    const VISA_CAVV_VALID_CODE = ["2", "3", "8", "A", "B"];
    const MC_CAVV_VALID_CODE = ["1", "2"];
    const AE_CAVV_VALID_CODE = ["2", "3", "8", "A"];

    private $response;

    /** @var OrderRepositoryInterface  */
    private $orderRepository;

    /** @var ConfigInterface  */
    protected $config;
    /**
     * @var Data
     */
    protected $_data;
    /**
     * @var QuoteFactory
     */
    protected $_quote;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * CompletePaymentHandler constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param ConfigInterface $config
     * @param Data $data
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ConfigInterface $config,
        Data $data,
        QuoteFactory $quoteFactory,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender
    ) {
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->_data = $data;
        $this->_quote = $quoteFactory;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $this->response = $response;
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $threeDSecureEnable = $this->config->getValue('three_d_secure');
        $canUse3DS = $payment->getAdditionalInformation('can_use_3ds');
        $nonAuthenticated = $payment->getAdditionalInformation('3ds_non_authenticated');

        if ($threeDSecureEnable && $canUse3DS) {
            $resultCode = $nonAuthenticated ? 'none' : $response[self::CAVV_RESULT_CODE] ?? 'null';
            $payment->setAdditionalInformation(
                'threed_secure_response_code',
                $resultCode
            );
            $paymentAction = $payment->getMethodInstance()->getConfigPaymentAction();
            if ($paymentAction == 'authorize') {
                $payment->authorize(
                    false,
                    $paymentDO->getOrder()->getGrandTotalAmount()
                );
            } else {
                $payment->setAdditionalInformation('3ds_non_authenticated', '');
                $payment->capture();
            }
            $order = $payment->getOrder();
            $this->orderRepository->save($order);
            if ($order->getCanSendNewEmailFlag()) {
                $this->orderSender->send($order);
                $invoice = current($order->getInvoiceCollection()->getItems());
                if ($invoice) {
                    $this->invoiceSender->send($invoice);
                }
            }
        }
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function checkCavvResultCode(array $response)
    {
        if (isset($response[self::CAVV_RESULT_CODE])) {
            switch ($response['CardType']) {
                case "V":
                    return in_array($response[self::CAVV_RESULT_CODE], self::VISA_CAVV_VALID_CODE);
                case "M":
                    return in_array($response[self::CAVV_RESULT_CODE], self::MC_CAVV_VALID_CODE);
                case "AX":
                    return in_array($response[self::CAVV_RESULT_CODE], self::AE_CAVV_VALID_CODE);
                default:
                    return false;
            }
        }

        return false;
    }
}
