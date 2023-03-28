<?php
namespace Tonder\Payment\Gateway\Response;

use Tonder\Payment\Gateway\Helper\RefundHelper;
use Tonder\Payment\Model\Adminhtml\Source\OrderHandlerAction;
use Magento\Checkout\Helper\Data;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Model\Config;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\ConfigInterface;

class CVDHandler implements HandlerInterface
{
    const XML_PATH_CVD_ENABLE = 'cvd_enable';
    const XML_PATH_CVD_FAIL = 'cvd_fail';
    const XML_PATH_CVD_NULL = 'cvd_null';

    const CVD_FAIL_CODE = ['1N','1D'];
    const CVD_NULL_CODE = ['1P','1S','1U','Other'];
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
    protected $_quote;

    /**
     * CVDHandler constructor.
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
        $this->_quote = $quoteFactory;
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

        if ($this->config->getValue(self::XML_PATH_CVD_ENABLE)) {
            if (isset($response['CvdResultCode']) && ($response['CvdResultCode'] != 'null' || isset($response['DataKey']))) {
                //CVD was Verified
                $payment->setAdditionalInformation(
                    'cvd_response_code',
                    $response['CvdResultCode']
                );

                //FAIL
                if (in_array($response['CvdResultCode'], self::CVD_FAIL_CODE)) {
                    $this->doOrderAction($this->config->getValue(self::XML_PATH_CVD_FAIL), $payment);
                    return;
                }

                //NULL
                if (in_array($response['CvdResultCode'], self::CVD_NULL_CODE)) {
                    $this->doOrderAction($this->config->getValue(self::XML_PATH_CVD_NULL), $payment);
                    return;
                }
            } else {
                //CVD was NOT Verified
                $payment->setAdditionalInformation(
                    'cvd_response_code',
                    'null'
                );
                $this->doOrderAction($this->config->getValue(self::XML_PATH_CVD_NULL), $payment);
                return;
            }
        }
    }

    /**
     * @param $action
     * @param $payment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function doOrderAction($action, $payment)
    {
        switch ($action) {
            case OrderHandlerAction::ORDER_ACTION_CANCEL:
                $quoteId = $payment->getOrder()->getQuoteId();
                $quoteModel = $this->_quote->create()->load($quoteId);
                $this->_data->sendPaymentFailedEmail($quoteModel, 'CVD code is not valid');
                throw new \Magento\Framework\Exception\LocalizedException(__('Your CVD code is not valid! Please check your payment information.'));
            case OrderHandlerAction::ORDER_ACTION_HOLD:
                $payment->setIsTransactionClosed(false);
                $payment->setAdditionalInformation('order_action', OrderHandlerAction::ORDER_ACTION_HOLD);
                $payment->setAdditionalInformation('order_action_handler_code', OrderHandlerAction::ORDER_ACTION_CVD_HANDLER);
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
