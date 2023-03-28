<?php
namespace Tonder\Payment\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaymentAction provides source for backend payment_action selector
 */
class OrderHandlerAction implements ArrayInterface
{
    const ORDER_ACTION_ACCEPT = 1;
    const ORDER_ACTION_CANCEL = 2;
    const ORDER_ACTION_HOLD = 3;
    const ORDER_ACTION_AVS_HANDLER = 1;
    const ORDER_ACTION_CVD_HANDLER = 2;
    const ORDER_ACTION_KOUNT_HANDLER = 3;
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ORDER_ACTION_ACCEPT,
                'label' => __('Accept Payment')
            ],
            [
                'value' => self::ORDER_ACTION_CANCEL,
                'label' => __('Reject Payment')
            ],
            [
                'value' => self::ORDER_ACTION_HOLD,
                'label' => __('Hold Payment')
            ]
        ];
    }
}
