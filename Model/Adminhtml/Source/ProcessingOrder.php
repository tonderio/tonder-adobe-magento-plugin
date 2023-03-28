<?php
namespace Tonder\Payment\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ProcessingOrder
 * @package Tonder\Anz\Model\Adminhtml\Source
 */
class ProcessingOrder implements ArrayInterface
{
    const STATUS_PAYMENT_DEFAULT = 'payment';
    const STATUS_PAYMENT_CUSTOM = 'payment_processing';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::STATUS_PAYMENT_DEFAULT,
                'label' => 'Payment',
            ],
            [
                'value' => self::STATUS_PAYMENT_CUSTOM,
                'label' => 'Processing Payment'
            ],
        ];
    }
}
