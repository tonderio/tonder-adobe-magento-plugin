<?php

namespace Tonder\Payment\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Tonder extends AbstractModel
{
    /**
     * Define model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Tonder\Payment\Model\ResourceModel\Tonder::class);
    }
}
