<?php

namespace Tonder\Payment\Model\ResourceModel\Tonder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Tonder\Payment\Model\Tonder::class, \Tonder\Payment\Model\ResourceModel\Tonder::class);
    }
}
