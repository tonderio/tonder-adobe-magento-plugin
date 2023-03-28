<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;

/**
 * Class ShippingAddressDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class BillingAddressDataBuilder extends AbstractDataBuilder implements BuilderInterface
{

    const PHONE = 'phone_number';
    const BILLING = 'billing';
    const ADDRESS = 'address';
    const POSTAL_CODE = 'postal_code';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const COMPANY_NAME = 'company_name';
    const CITY = 'city';
    const COUNTRY = 'country';
    const PROVINCE = 'province';
    const FAX = 'fax';
    const TAX1 = 'tax1';
    const TAX2 = 'tax2';
    const TAX3 = 'tax3';
    const SHIPPING_COST = 'shipping_cost';

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

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();
        $billing = [
            self::FIRST_NAME => $billingAddress->getFirstname(),
            self::LAST_NAME => $billingAddress->getLastname(),
            self::ADDRESS => $billingAddress->getStreetLine1(),
            self::COMPANY_NAME => $billingAddress->getCompany() ? $billingAddress->getCompany() : 'none',
            self::CITY => $billingAddress->getCity(),
            self::PROVINCE => $billingAddress->getRegionCode() ?? 'none',
            self::PHONE => $billingAddress->getTelephone(),
            self::FAX => $billingAddress->getTelephone(),
            self::COUNTRY => $billingAddress->getCountryId(),
            self::POSTAL_CODE => $billingAddress->getPostcode(),
            self::TAX1 => sprintf('%.2F', $order->getBaseTaxAmount()),
            self::TAX2 => '0',
            self::TAX3 => '0',
            self::SHIPPING_COST => sprintf('%.2F', $order->getBaseShippingAmount())
        ];
        if ($billingAddress->getRegionCode()) {
            $billing[BillingAddressDataBuilder::PROVINCE] = $billingAddress->getRegionCode();
        }
        return [
            self::REPLACE_KEY => [
                CustomerDataBuilder::CUSTOMER => [
                    self::BILLING => $billing
                ]
            ]
        ];
    }
}
