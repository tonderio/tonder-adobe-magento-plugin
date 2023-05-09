<?php
namespace Tonder\Payment\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ConnectionType
 * @package Tonder\Payment\Model\Adminhtml\Source
 */
class ConnectionType implements ArrayInterface
{
    const CONNECTION_TYPE_DIRECT = 'direct';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CONNECTION_TYPE_DIRECT,
                'label' => 'Direct connection',
            ]
        ];
    }
}
