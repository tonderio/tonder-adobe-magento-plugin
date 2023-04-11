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

        $result = [
            'id_product' => [],
            'quantity_product' => 0,
            'id_ship' => "0",
            'instance_id_ship' => "2",
            'title_ship' => 'shipping',
        ];

        /** @var \Magento\Sales\Model\Order\Item $item */

        foreach ($order->getItems() as $item) {
            if (!$item->getParentItem()) {
                $result['id_product'][] = $item->getProductId();
                $result['quantity_product'] += $item->getQtyOrdered();
            }
        }

        $result['id_product'] = implode(",", $result['id_product']);

        return $result;
    }
}
