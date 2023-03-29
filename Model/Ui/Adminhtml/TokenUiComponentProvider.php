<?php

namespace Tonder\Payment\Model\Ui\Adminhtml;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;

/**
 * Class TokenProvider
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     */
    private $componentFactory;
    /**
     * @var Json
     */
    protected $_jsonFramework;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param Json $_jsonFramework
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        Json $_jsonFramework
    ) {
        $this->componentFactory = $componentFactory;
        $this->_jsonFramework = $_jsonFramework;
    }

    /**
     * @inheritdoc
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $data = $this->_jsonFramework->unserialize($paymentToken->getTokenDetails());
        $component = $this->componentFactory->create(
            [
                'config' => [
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $data,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
                    'template' => 'Tonder_Payment::form/vault.phtml'
                ],
                'name' => Template::class
            ]
        );

        return $component;
    }
}
