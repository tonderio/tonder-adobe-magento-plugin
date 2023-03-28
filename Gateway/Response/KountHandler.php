<?php

namespace Tonder\Payment\Gateway\Response;

use Tonder\Payment\Gateway\Helper\RefundHelper;
use Tonder\Payment\Model\Adminhtml\Source\OrderHandlerAction;
use Magento\Checkout\Helper\Data;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order\Payment;

class KountHandler implements HandlerInterface
{
    const XML_PATH_KOUNT_ENABLE = 'kount_enable';
    const KOUNT_NULL = 'kount_null';
    const KOUNT_DECLINE = 'kount_decline';
    const KOUNT_REVIEW = 'kount_review';
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var RefundHelper
     */
    private $refundHelper;

    /**
     * @var
     */
    private $response;

    /**
     * @var Data
     */
    protected $_data;

    /**
     * @var QuoteFactory
     */
    protected $quote;

    /**
     * AVSHandler constructor.
     * @param ConfigInterface $config
     * @param RefundHelper $refundHelper
     * @param Data $data
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        ConfigInterface $config,
        RefundHelper $refundHelper,
        Data $data,
        QuoteFactory $quoteFactory
    ) {
        $this->config = $config;
        $this->refundHelper = $refundHelper;
        $this->_data = $data;
        $this->quote = $quoteFactory;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $this->response = $response;
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        if ($this->config->getValue(self::XML_PATH_KOUNT_ENABLE)) {
            if (isset($response['KountResult']) && $response['KountResult'] != 'null') {
                $payment->setAdditionalInformation('kount_response_code', $response['KountResult']);
                $payment->setAdditionalInformation('kount_transaction_id', $response['KountTransactionId']);
                if ($response['KountResult'] == 'D') {
                    $message = 'Your payment has been decline by Kount';
                    $this->doOrderAction($this->config->getValue(self::KOUNT_DECLINE), $payment, $message);
                    return;
                } elseif ($response['KountResult'] == 'R') {
                    $message = 'Your payment has been review by Kount';
                    $this->doOrderAction($this->config->getValue(self::KOUNT_REVIEW), $payment, $message);
                    return;
                }
            } else {
                $payment->setAdditionalInformation('kount_response_code', 'null');
                $message = 'The Kount response code is NULL';
                $this->doOrderAction($this->config->getValue(self::KOUNT_NULL), $payment, $message);
                return;
            }
        }
    }

    /**
     * @param $action
     * @param $payment
     * @param $message
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function doOrderAction($action, $payment, $message)
    {
        switch ($action) {
            case OrderHandlerAction::ORDER_ACTION_CANCEL:
                $quoteId = $payment->getOrder()->getQuoteId();
                $quoteModel = $this->quote->create()->load($quoteId);
                $this->_data->sendPaymentFailedEmail($quoteModel, $message);
                throw new \Magento\Framework\Exception\LocalizedException(__($message));
            case OrderHandlerAction::ORDER_ACTION_HOLD:
                $payment->setIsTransactionClosed(false);
                $payment->setAdditionalInformation('order_action', OrderHandlerAction::ORDER_ACTION_HOLD);
                $payment->setAdditionalInformation('order_action_handler_code', OrderHandlerAction::ORDER_ACTION_KOUNT_HANDLER);
                return;
            default:
                return;
        }
    }

    /**
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    private function refund()
    {
        $this->refundHelper->setResponse($this->response);
        $this->refundHelper->refund();
    }
}
