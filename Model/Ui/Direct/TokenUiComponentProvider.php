<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tonder\Payment\Model\Ui\Direct;

use Tonder\Payment\Block\Payment;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;

/**
 * Class TokenUiComponentProvider
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     */
    private $componentFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;
    /**
     * @var Json
     */
    protected $_jsonFramework;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param ScopeConfigInterface $config
     * @param Json $_jsonFramework
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        ScopeConfigInterface $config,
        Json $_jsonFramework
    ) {
        $this->componentFactory = $componentFactory;
        $this->config = $config;
        $this->_jsonFramework = $_jsonFramework;
    }

    /**
     * Get UI component for token
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $jsonDetails = $this->_jsonFramework->unserialize($paymentToken->getTokenDetails());
        $connectionType = $this->config->getValue('payment/moneris/connection_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => Payment::MONERIS_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
                    'connectionType' => $connectionType
                ],
                'name' => 'Tonder_Moneris/js/view/payment/method-renderer/vault'
            ]
        );

        return $component;
    }
}
