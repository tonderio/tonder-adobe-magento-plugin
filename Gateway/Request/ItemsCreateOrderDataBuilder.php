<?php
declare(strict_types=1);

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ItemsCreateOrderDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class ItemsCreateOrderDataBuilder extends AbstractDataBuilder
{
    const ITEMS = 'items';


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
        $result = [];

        /** @var \Magento\Sales\Model\Order\Item $item */

        foreach ($order->getItems() as $item) {
            $result[] = [
                "description" => $item->getDescription() ?? "",
                "product_reference" => $item->getName() ?? "",
                'quantity' => $item->getQtyOrdered() ?? 0,
                'price_unit' => $item->getPrice() ?? 0,
                'discount' => $item->getDiscountAmount() ?? 0,
                'taxes' => $item->getTaxAmount() ?? 0,
                "amount_total" => $item->getRowTotal() ?? 0
            ];
        }

        return [
            self::ITEMS =>  $result,
        ];

    }
}
