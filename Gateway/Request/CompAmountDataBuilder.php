<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Tonder\Payment\Gateway\Helper\MappingCurrency;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CompAmountDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class CompAmountDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * @var MappingCurrency
     */
    protected $mappingCurrency;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CompAmountDataBuilder constructor.
     * @param MappingCurrency $mappingCurrency
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(MappingCurrency $mappingCurrency, StoreManagerInterface $storeManager)
    {
        $this->mappingCurrency = $mappingCurrency;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentData = $buildSubject['payment'];
        $code = $paymentData->getPayment()->getOrder()->getOrderCurrencyCode();
        $additionalInformation = $paymentData->getPayment()->getAdditionalInformation();
        $amount = SubjectReader::readAmount($buildSubject);
        if (isset($additionalInformation['mcp_purchase']) && $additionalInformation['mcp_purchase'] == 'Yes') {
            $currencyData = $this->mappingCurrency->getCurrencyData($code);
            if ($currencyData['currency_code_number'] != '124') {
                $amount = $currencyData['rate'] > 1 ? (int)round($paymentData->getPayment()->getAmountOrdered() * 100) : (int)round($paymentData->getPayment()->getAmountOrdered());
                return [
                    self::REPLACE_KEY => [
                        self::CARDHOLDER_AMOUNT => sprintf($amount),
                        self::CARDHOLDER_CURRENCY_CODE => $currencyData['currency_code_number'],
                        self::MCP_VERSION => '1.0'
                    ]
                ];
            } else {
                $amount =  round($paymentData->getPayment()->getAmountOrdered(), 2);
                return [
                    self::REPLACE_KEY => [
                        self::COMP_AMOUNT => sprintf('%.2F', $amount)
                    ]
                ];
            }
        }
        return [
            self::REPLACE_KEY => [
                self::COMP_AMOUNT => sprintf('%.2F', $amount)
            ]
        ];
    }
}
