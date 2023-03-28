<?php
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CustomerDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class CustomerDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * Customer block name
     */
    const CUSTOMER = 'cust_info';

    /**
     * The customer’s email address, which must be correctly formatted if present
     */
    const EMAIL = 'email';

    const INSTRUCTIONS = 'instructions';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        if (!$billingAddress) {
            return [];
        }

        return [
            self::REPLACE_KEY => [
                self::CUSTOMER => [
                    self::EMAIL => $billingAddress->getEmail(),
                    self::INSTRUCTIONS => 'Magento 2 Moneris Payment'
                ]
            ]
        ];
    }
}
