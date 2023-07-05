<?php

namespace Tonder\Payment\Gateway\Http;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Tonder\Payment\Gateway\Request\AbstractDataBuilder;
use Tonder\Payment\Gateway\Request\TransactionDataBuilder;
use Tonder\Payment\Model\Adminhtml\Source\Environment;
use Magento\Framework\Xml\GeneratorFactory;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;

/**
 * Class AbstractTransferFactory
 */
abstract class AbstractTransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var TransferBuilder
     */
    protected $transferBuilder;

    /**
     * @var GeneratorFactory
     */
    protected $generator;

    /**
     * Transaction Type
     *
     * @var string
     */
    private $action;

    /**
     * @var OrderInterfaceFactory
     */
    protected $order;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Url Path
     *
     * @var string
     */
    private $path;

    /**
     * AbstractTransferFactory constructor.
     *
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     * @param GeneratorFactory $generator
     * @param OrderInterfaceFactory $order
     * @param EncryptorInterface $encryptor
     * @param SerializerInterface $serializer
     * @param null $action
     * @param string $path
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        GeneratorFactory $generator,
        OrderInterfaceFactory $order,
        EncryptorInterface $encryptor,
        SerializerInterface $serializer,
        $action = null,
        $path = ''
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
        $this->generator = $generator;
        $this->order = $order;
        $this->action = $action;
        $this->path = $path;
        $this->serializer = $serializer;
        $this->encryptor = $encryptor;
    }

    /**
     * @return null|string
     */
    private function getAction()
    {
        return $this->action;
    }

    private function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    protected function isUsCountry()
    {
        if ($this->config->getValue('environment') == Environment::ENVIRONMENT_US) {
            return true;
        }

        return false;
    }

    /**
     * Get request URL
     *
     * @param string $additionalPath
     * @return string
     */
    public function getUrl($additionalPath = '', $is3DS = false)
    {
        $mode = (int)$this->config->getValue('mode');
        $baseUrl = '';

        switch ($mode) {
            case 1:
                $baseUrl = $this->config->getValue('stage_api');
                break;
            case 2:
                $baseUrl = $this->config->getValue('live_api');
                break;
            default:
                $baseUrl = $this->config->getValue('mock_api');
                break;
        }
        return $baseUrl . $this->getPath();
    }

    public function getToken()
    {
        return $this->encryptor->decrypt($this->config->getValue('token'));
    }

    public function getCredentials()
    {
        $mode = $this->config->getValue('mode');
        if ($mode == 1) {
            $username = "stage_username";
            $password = "stage_password";
        } else if ($mode == 2) {
            $username = "live_username";
            $password = "live_password";
        } else {
            $username = "mock_username";
            $password = "mock_password";
        }

        return [
            $this->encryptor->decrypt($this->config->getValue($username)),
            $this->encryptor->decrypt($this->config->getValue($password)),
        ];
    }

    /**
     * Convert to XML and replace some tags don't need
     *
     * @param $request
     * @param string $type
     * @return string|string[]
     * @throws \DOMException
     */
    protected function convertToXml($request, $type = "request")
    {
        if (isset($request[AbstractDataBuilder::REPLACE_KEY])) {
            $prefix = $this->isUsCountry() ? 'us_' : '';
            $action = $prefix . $this->getAction();
            if (isset($request[AbstractDataBuilder::REPLACE_KEY][TransactionDataBuilder::ORDER_ID])) {
                $orderId = $request[AbstractDataBuilder::REPLACE_KEY][TransactionDataBuilder::ORDER_ID];
                if ($this->getAction() == AbstractDataBuilder::PURCHASE) {
                    $action = $this->checkMCPPurchase($request) ? AbstractDataBuilder::MCP_PURCHASE : AbstractDataBuilder::PURCHASE;
                } elseif ($this->getAction() == AbstractDataBuilder::REFUND) {
                    $action = $this->exceptCADCurrency($orderId, $request) ? AbstractDataBuilder::MCP_REFUND : AbstractDataBuilder::REFUND;
                } elseif ($this->getAction() == AbstractDataBuilder::AUTHORIZE) {
                    $action = $this->checkMCPPurchase($request) ? AbstractDataBuilder::MCP_AUTHORIZE : AbstractDataBuilder::AUTHORIZE;
                } elseif ($this->getAction() == AbstractDataBuilder::PRE_AUTH_CAPTURE) {
                    $action = $this->exceptCADCurrency($orderId, $request) ? AbstractDataBuilder::MCP_PRE_AUTH_CAPTURE : AbstractDataBuilder::PRE_AUTH_CAPTURE;
                } elseif ($this->getAction() == AbstractDataBuilder::VAULT_AUTHORIZE) {
                    $action = $this->checkMCPPurchase($request) ? AbstractDataBuilder::MCP_VAULT_AUTHORIZE : AbstractDataBuilder::VAULT_AUTHORIZE;
                } elseif ($this->getAction() == AbstractDataBuilder::VAULT_CAPTURE) {
                    $action = $this->checkMCPPurchase($request) ? AbstractDataBuilder::MCP_VAULT_CAPTURE : AbstractDataBuilder::VAULT_CAPTURE;
                }
            }
            if ($type != 'request') {
                $action = $request[AbstractDataBuilder::REPLACE_KEY]['type'];
                unset($request[AbstractDataBuilder::REPLACE_KEY]['type']);
            }
            $request[$action] = $request[AbstractDataBuilder::REPLACE_KEY];
            unset($request[AbstractDataBuilder::REPLACE_KEY]);
        }
        $request = [$type => $request];
        $xml = $this->generator->create()->arrayToXml($request);
        $xml = str_replace($this->listTagsNeedReplace(), '', $xml);

        return $xml;
    }

    /**
     * List Tags need removed
     *
     * @return array
     */
    public function listTagsNeedReplace()
    {
        return [
            '<items>',
            '</items>'
        ];
    }

    /**
     * @param $orderId
     * @param $request
     * @return bool
     */
    public function exceptCADCurrency($orderId, $request)
    {
        $enableMCPPurchase = $this->getMCPPurchaseStatus($orderId);
        return $enableMCPPurchase == 'Yes' && isset($request[AbstractDataBuilder::REPLACE_KEY]['mcp_version']);
    }

    /**
     * @param $orderId
     * @return string[]
     */
    public function getMCPPurchaseStatus($orderId)
    {
        return $this->order->create()->loadByIncrementId($orderId)->getPayment()->getAdditionalInformation('mcp_purchase');
    }

    /**
     * @param $request
     * @return bool
     */
    public function checkMCPPurchase($request)
    {
        $multiCurrency = $this->config->getValue('multi_currency');
        return $multiCurrency && isset($request[AbstractDataBuilder::REPLACE_KEY]['mcp_version']);
    }
}
