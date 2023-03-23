<?php

namespace Tonder\Payment\ViewModel;

class PaymentSettings implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Tonder\Payment\Helper\Data
     */
    protected $helper;

    /**
     *
     */
    public function __construct(
        \Tonder\Payment\Helper\Data $helper
    ) {
        $this->helper= $helper;
    }

    /**
     *
     */
    public function getPublicKey()
    {
        return $this->helper->getPublicKey();
    }

    /**
     *
     */
    public function getActiveMode()
    {
        return $this->helper->getActiveMode();
    }
    
    /**
     *
     */
    public function getDebugMode()
    {
        return $this->helper->debugMode();
    }
    
    /**
     *
     */
    public function getStoreCurrencyCode()
    {
        return $this->helper->getStoreCurrencyCode();
    }
    
    /**
     *
     */
    public function getPaymentMethodStatus()
    {
    	return $this->helper->getPaymentMethodStatus();
    }
}
