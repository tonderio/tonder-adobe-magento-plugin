<?php

namespace Tonder\Payment\Block\Onepage;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * Get last order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }
}
