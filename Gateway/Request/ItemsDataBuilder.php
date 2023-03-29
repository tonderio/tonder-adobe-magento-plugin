<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ItemsDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class ItemsDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const ITEMS = 'items';
    const SKU = 'product_code';
    const QUANTITY = 'quantity';
    const UNIT_COST = 'extended_amount';
    const NAME = 'name';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ItemsDataBuilder constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        return [
            self::REPLACE_KEY => [
                CustomerDataBuilder::CUSTOMER => [
                    self::ITEMS => $this->prepareItems($order->getItems())
                ]
            ]
        ];
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface[]|null $items
     * @return array
     */
    private function prepareItems($items)
    {
        $result = [];
        $multiCurrency = $this->scopeConfig->getValue('payment/tonder/multi_currency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        /** @var \Magento\Sales\Model\Order\Item $item */

        foreach ($items as $item) {
            if (!$item->getParentItem()) {
                $extendAmount = $multiCurrency ? number_format($item->getPrice(), 2) : number_format($item->getBasePrice(), 2);
                $result[] = [
                    self::SKU => $item->getSku(),
                    self::QUANTITY => number_format($item->getQtyOrdered(), 0),
                    self::UNIT_COST => $extendAmount,
                    self::NAME => $item->getName()
                ];
            }

        }

        return $result;
    }
}
