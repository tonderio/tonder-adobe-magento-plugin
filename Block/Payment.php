<?php
namespace Tonder\Payment\Block;

use Tonder\Payment\Model\Adminhtml\Source\ConnectionType;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class Payment
 */
class Payment extends Template
{
    const TONDER_CODE = 'tonder';
    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var Json
     */
    protected $_jsonFramework;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigInterface $config
     * @param array $data
     * @param Json $_jsonFramework
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        Json $_jsonFramework,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->_jsonFramework = $_jsonFramework;
    }

    /**
     * @return string
     */
    public function getPaymentConfig()
    {
        return $this->_jsonFramework->serialize(
            [
                'code' => self::TONDER_CODE,
            ]
        );
    }

    /**
     * @return string
     */
    public function getConnectionType()
    {
        return ConnectionType::CONNECTION_TYPE_DIRECT;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return self::TONDER_CODE;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        if ($this->config->getValue('connection_type') !== ConnectionType::CONNECTION_TYPE_DIRECT) {
            return '';
        }

        return parent::toHtml();
    }
}
