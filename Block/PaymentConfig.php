<?php

namespace Tonder\Payment\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;

class PaymentConfig extends Template
{
    const TONDER_CVD_ENABLE_PATH = 'payment/tonder/cvd_enable';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function cvdEnable()
    {
        return $this->scopeConfig->getValue(self::TONDER_CVD_ENABLE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
