<?php

namespace Tonder\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Tonder extends AbstractDb
{
    /**
     * Define primary key
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tonder_payment', 'id');
    }
}
