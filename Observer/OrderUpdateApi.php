<?php

namespace Tonder\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderUpdateApi implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Tonder\Payment\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Tonder\Payment\Helper\Data $helper
     */

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Tonder\Payment\Helper\Data $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * Observer execution
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->checkoutSession->getTonderOrderId()) {
            $this->helper->tonderOrderUpdate(
                $this->checkoutSession->getTonderOrderId(),
                $this->checkoutSession->getAntiFraudMeta()
            );
        }
    }
}
