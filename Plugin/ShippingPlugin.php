<?php

namespace Tonder\Payment\Plugin;

class ShippingPlugin
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
     * TODO
     *
     * @param \Magento\Quote\Model\Quote\Address\Total\Shipping $subject
     * @param array $result
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $result
     */
    public function afterFetch(
        \Magento\Quote\Model\Quote\Address\Total\Shipping $subject,
        $result,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {

        if ($this->checkoutSession->getTonderOrderId()) {
            $this->helper->tonderOrderUpdate(
                $this->checkoutSession->getTonderOrderId(),
                $this->checkoutSession->getAntiFraudMeta()
            );
        }
        return $result;
    }
}
