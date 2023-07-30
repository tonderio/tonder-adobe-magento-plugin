<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class TransactionIdDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class TransactionIdDataBuilder extends AbstractDataBuilder
{
    /**
     * A unique identifier that represents the transaction in eWAY’s system
     */
    const TRANSACTION_ID = 'transaction_id';

    const TRANSACTION_CHARGE = 'transaction_charge';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        return [
            self::TRANSACTION_ID => $paymentDO->getPayment()->getParentTransactionId(),
            self::TRANSACTION_CHARGE => $paymentDO->getPayment()->getAdditionalInformation('reference_num')
        ];
    }
}
