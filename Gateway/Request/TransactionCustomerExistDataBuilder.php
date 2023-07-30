<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CustomerDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class TransactionCustomerExistDataBuilder extends AbstractDataBuilder
{
    const CUSTOMER_EMAIL = "customer_email";
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        /** @var Payment $payment */
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        if (!$billingAddress) {
            return [];
        }

        return [
            self::CUSTOMER_EMAIL => $billingAddress->getEmail()
        ];
    }
}
