<?php

namespace Tonder\Payment\Plugin\Model;

class MethodList
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
     * Update tonder order
     *
     * @param \Magento\Payment\Model\MethodList $subject
     * @param array $availableMethods
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return $availableMethods
     */

    public function afterGetAvailableMethods(
        \Magento\Payment\Model\MethodList $subject,
        $availableMethods,
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        foreach ($availableMethods as $key => $method) {
            if ($method->getCode() == 'tonder') {
                if ($this->checkoutSession->getTonderOrderId()) {
                    $this->helper->tonderOrderUpdate(
                        $this->checkoutSession->getTonderOrderId(),
                        $this->checkoutSession->getAntiFraudMeta()
                    );
                }
            }
        }
        return $availableMethods;
    }
}
