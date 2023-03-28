<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TransactionDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class TransactionDataBuilder extends AbstractDataBuilder implements BuilderInterface
{

    const ORDER_ID = 'order_id';

    const AMOUNT = 'amount';

    const CUSTOMER_ID = 'cust_id';

    const CRYPT_TYPE = 'crypt_type';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $data = [
            self::ORDER_ID => $order->getOrderIncrementId(),
            self::CRYPT_TYPE => '7'
        ];

        if ($order->getCustomerId()) {
            $data[self::CUSTOMER_ID] = (string)$order->getCustomerId();
        }
        return [
            self::REPLACE_KEY => $data
        ];
    }
}
