<?php

namespace Tonder\Payment\Model\Config\Source;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Mock Server (Test)')], ['value' => 1, 'label' => __('Stage Server (Test)')], ['value' => 2, 'label' => __('Live  Server')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            0 => __('Mock Server (Test)'),
            1 => __('Stage Server (Test)'),
            2 => __('Live  Server')
        ];
    }
}
