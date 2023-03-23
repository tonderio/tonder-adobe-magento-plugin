<?php

namespace Tonder\Payment\Block\Sales\Order\Invoice;

use Magento\Framework\View\Element\Template;
use Magento\Tax\Model\Config;
use Magento\Framework\DataObject;

class AdjustmentAmount extends Template
{
    /**
     * @var \Magento\Tax\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $source;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    private $invoice;

    /**
     * @param Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $taxConfig,
        array $data = []
    ) {
        $this->config = $taxConfig;
        parent::__construct($context, $data);
    }

    /**
     * Is display full summary enabled
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get source
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get store object
     *
     * @return mixed
     */
    public function getStore()
    {
        return $this->order->getStore();
    }

    /**
     * Get order object
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get invoice object
     *
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Get label properties
     *
     * @return mixed
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get value properties
     *
     * @return mixed
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize invoice adjustment totals
     *
     * @return \Tonder\Payment\Block\Sales\Order\Invoice\AdjustmentAmount
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->order = $parent->getOrder();
        $this->source = $parent->getSource();
        $this->invoice = $parent->getInvoice();

        $adjustment = new DataObject(
            [
                'code' => 'adjustment_amount',
                'strong' => false,
                'value' => $this->invoice->getAdjustmentAmount(),
                'base_value' => $this->invoice->getAdjustmentAmount(),
                'label' => __('Adjustment Amount'),
            ]
        );
        $parent->addTotal($adjustment, 'adjustment_amount');

        return $this;
    }
}
