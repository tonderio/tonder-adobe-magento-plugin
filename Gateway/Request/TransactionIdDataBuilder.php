<?php
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TransactionIdDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class TransactionIdDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * A unique identifier that represents the transaction in eWAY’s system
     */
    const TRANSACTION_ID = 'transaction_id';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        return [
            self::TRANSACTION_ID => $paymentDO->getPayment()->getParentTransactionId()
        ];
    }
}
