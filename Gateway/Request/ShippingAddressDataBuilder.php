<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class ShippingAddressDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class ShippingAddressDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const SHIPPING = 'shipping';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $shippingAddress = $order->getShippingAddress();

        if (!$shippingAddress) {
            return [];
        }

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();
        $shipping = [
            BillingAddressDataBuilder::FIRST_NAME => $shippingAddress->getFirstname(),
            BillingAddressDataBuilder::LAST_NAME => $shippingAddress->getLastname(),
            BillingAddressDataBuilder::ADDRESS => $shippingAddress->getStreetLine1(),
            BillingAddressDataBuilder::COMPANY_NAME => $shippingAddress->getCompany() ? $shippingAddress->getCompany() : 'none',
            BillingAddressDataBuilder::CITY => $shippingAddress->getCity(),
            BillingAddressDataBuilder::PROVINCE => $shippingAddress->getRegionCode() ?? 'none',
            BillingAddressDataBuilder::PHONE => $shippingAddress->getTelephone(),
            BillingAddressDataBuilder::FAX => $shippingAddress->getTelephone(),
            BillingAddressDataBuilder::COUNTRY => $shippingAddress->getCountryId(),
            BillingAddressDataBuilder::POSTAL_CODE => $shippingAddress->getPostcode(),
            BillingAddressDataBuilder::TAX1 => sprintf('%.2F', $order->getBaseTaxAmount()),
            BillingAddressDataBuilder::TAX2 => '0',
            BillingAddressDataBuilder::TAX3 => '0',
            BillingAddressDataBuilder::SHIPPING_COST => sprintf('%.2F', $order->getBaseShippingAmount())
        ];
        if ($shippingAddress->getRegionCode()) {
            $shipping[BillingAddressDataBuilder::PROVINCE] = $shippingAddress->getRegionCode();
        }
        return [
            self::REPLACE_KEY => [
                CustomerDataBuilder::CUSTOMER => [
                    self::SHIPPING => $shipping
                ]
            ]
        ];
    }
}
