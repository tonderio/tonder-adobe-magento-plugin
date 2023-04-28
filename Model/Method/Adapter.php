<?php

namespace Tonder\Payment\Model\Method;

use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Adapter
 */
class Adapter implements MethodInterface
{
    const DIRECT_FACADE = 'TonderFacade';

    const METHOD_CODE = 'tonder';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var MethodInterface
     */
    private $paymentInstance;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ConfigInterface $config, ObjectManagerInterface $objectManager)
    {
        $this->config        = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @return MethodInterface
     */
    private function getPaymentInstance()
    {
        if (!isset($this->paymentInstance)) {
            $this->paymentInstance = $this->objectManager->create(self::DIRECT_FACADE);
        }

        return $this->paymentInstance;
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->getPaymentInstance()->getCode();
    }

    /**
     * @inheritdoc
     */
    public function getFormBlockType()
    {
        return $this->getPaymentInstance()->getFormBlockType();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getPaymentInstance()->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function setStore($storeId)
    {
        $this->getPaymentInstance()->setStore($storeId);
    }

    /**
     * @inheritdoc
     */
    public function getStore()
    {
        return $this->getPaymentInstance()->getStore();
    }

    /**
     * @inheritdoc
     */
    public function canOrder()
    {
        return $this->getPaymentInstance()->canOrder();
    }

    /**
     * @inheritdoc
     */
    public function canAuthorize()
    {
        return $this->getPaymentInstance()->canAuthorize();
    }

    /**
     * @inheritdoc
     */
    public function canCapture()
    {
        return $this->getPaymentInstance()->canCapture();
    }

    /**
     * @inheritdoc
     */
    public function canCapturePartial()
    {
        return $this->getPaymentInstance()->canCapturePartial();
    }

    /**
     * @inheritdoc
     */
    public function canCaptureOnce()
    {
        return $this->getPaymentInstance()->canCaptureOnce();
    }

    /**
     * @inheritdoc
     */
    public function canRefund()
    {
        return $this->getPaymentInstance()->canRefund();
    }

    /**
     * @inheritdoc
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->getPaymentInstance()->canRefundPartialPerInvoice();
    }

    /**
     * @inheritdoc
     */
    public function canVoid()
    {
        return $this->getPaymentInstance()->canVoid();
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        return $this->getPaymentInstance()->canUseInternal();
    }

    /**
     * @inheritdoc
     */
    public function canUseCheckout()
    {
        return $this->getPaymentInstance()->canUseCheckout();
    }

    /**
     * @inheritdoc
     */
    public function canEdit()
    {
        return $this->getPaymentInstance()->canEdit();
    }

    /**
     * @inheritdoc
     */
    public function canFetchTransactionInfo()
    {
        return $this->getPaymentInstance()->canFetchTransactionInfo();
    }

    /**
     * @inheritdoc
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return $this->getPaymentInstance()->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * @inheritdoc
     */
    public function isGateway()
    {
        return $this->getPaymentInstance()->isGateway();
    }

    /**
     * @inheritdoc
     */
    public function isOffline()
    {
        return $this->getPaymentInstance()->isOffline();
    }

    /**
     * @inheritdoc
     */
    public function isInitializeNeeded()
    {
        return $this->getPaymentInstance()->isInitializeNeeded();
    }

    /**
     * @inheritdoc
     */
    public function canUseForCountry($country)
    {
        return $this->getPaymentInstance()->canUseForCountry($country);
    }

    /**
     * @inheritdoc
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->getPaymentInstance()->canUseForCurrency($currencyCode);
    }

    /**
     * @inheritdoc
     */
    public function getInfoBlockType()
    {
        return $this->getPaymentInstance()->getInfoBlockType();
    }

    /**
     * @inheritdoc
     */
    public function getInfoInstance()
    {
        return $this->getPaymentInstance()->getInfoInstance();
    }

    /**
     * @inheritdoc
     */
    public function setInfoInstance(InfoInterface $info)
    {
        $this->getPaymentInstance()->setInfoInstance($info);
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return $this->getPaymentInstance()->validate();
    }

    /**
     * @inheritdoc
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->getPaymentInstance()->order($payment, $amount);
    }

    /**
     * @inheritdoc
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->getPaymentInstance()->authorize($payment, $amount);
    }

    /**
     * @inheritdoc
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->getPaymentInstance()->capture($payment, $amount);
    }

    /**
     * @inheritdoc
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->getPaymentInstance()->refund($payment, $amount);
    }

    /**
     * @inheritdoc
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->getPaymentInstance()->cancel($payment);
    }

    /**
     * @inheritdoc
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->getPaymentInstance()->void($payment);
    }

    /**
     * @inheritdoc
     */
    public function canReviewPayment()
    {
        return $this->getPaymentInstance()->canReviewPayment();
    }

    /**
     * @inheritdoc
     */
    public function acceptPayment(InfoInterface $payment)
    {
        return $this->getPaymentInstance()->acceptPayment($payment);
    }

    /**
     * @inheritdoc
     */
    public function denyPayment(InfoInterface $payment)
    {
        return $this->getPaymentInstance()->denyPayment($payment);
    }

    /**
     * @inheritdoc
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->getPaymentInstance()->getConfigData($field, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        return $this->getPaymentInstance()->assignData($data);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->getPaymentInstance()->isAvailable($quote);
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null)
    {
        return $this->getPaymentInstance()->isActive($storeId);
    }

    /**
     * @inheritdoc
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this->getPaymentInstance()->initialize($paymentAction, $stateObject);
    }

    /**
     * @inheritdoc
     */
    public function getConfigPaymentAction()
    {
        return $this->getPaymentInstance()->getConfigPaymentAction();
    }

    public function getInstructions()
    {
        $instructions = $this->getPaymentInstance()->getConfigData('instructions');
        return $instructions !== null ? trim($instructions) : '';
    }

    public function getFormConfiguration()
    {
        return [
            'cardholder_name' => $this->getFormField('cardholder_name'),
            'card_number' => $this->getFormField('card_number'),
            'expiration_date' => $this->getFormField('expiration_date'),
            'month' => $this->getFormField('month'),
            'year' => $this->getFormField('year'),
            'month_labels' => $this->getMonthLabels(),
            'card_verification_number' => $this->getFormField('card_verification_number'),
            'card_tooltip_message' => $this->getFormField('card_tooltip_message'),
        ];
    }

    public function getFormField($path)
    {
        return $this->getPaymentInstance()->getConfigData('form_configuration/' . $path);
    }

    public function getMonthLabels()
    {
        $monthLabels = [];
        foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec',] as $index => $month) {
            $monthLabels[$index] = $this->getFormField($month);
        }
        return $monthLabels;
    }
}
