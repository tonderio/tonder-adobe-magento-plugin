<?php

namespace Tonder\Payment\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    public const SANDBOX = 2;
    public const PRODUCTION = 3;

    /**
     * Available modes
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SANDBOX,
                'label' => __('Sandbox')
            ],
            [
                'value' => self::PRODUCTION,
                'label' => __('Production')
            ]
        ];
    }
}
