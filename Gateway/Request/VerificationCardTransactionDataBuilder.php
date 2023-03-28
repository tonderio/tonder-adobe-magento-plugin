<?php
/**
 * Copyright © Tonder JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 11/05/2020
 * Time: 16:46
 */

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class VerificationCardTransactionDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const ORDER_ID = 'order_id';

    const AMOUNT = 'amount';

    const CRYPT_TYPE = 'crypt_type';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        return [
            self::REPLACE_KEY => [
                self::ORDER_ID => "VerifyCard-" . $order->getOrderIncrementId() . "-" . time(),
                self::CRYPT_TYPE => '7', //TODO change it
            ]
        ];
    }
}
