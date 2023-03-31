<?php
namespace Tonder\Payment\Model\Ui\Direct;

use Tonder\Payment\Block\Payment;
use Tonder\Payment\Model\Adminhtml\Source\Environment;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ConfigInterface
     */
    private $vaultConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param ConfigInterface $vaultConfig
     * @param UrlInterface $urlBuilder
     * @param Session $customerSession
     */
    public function __construct(
        ConfigInterface $config,
        ConfigInterface $vaultConfig,
        UrlInterface $urlBuilder,
        Session $customerSession
    ) {
        $this->config = $config;
        $this->vaultConfig = $vaultConfig;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                Payment::TONDER_CODE => [
                    'connectionType' => $this->config->getValue('connection_type'),
                    'cvd' => (int)$this->config->getValue('cvd_enable'),
                    'orderCancelUrl' => $this->urlBuilder->getUrl(
                        'tonder/order/cancel',
                        ['_secure' => true]
                    ),
                    'paymentUrl' => $this->getPaymentUrl(),
                    'hppid' => $this->config->getValue('hpp_id'),
                    'hppkey' => $this->config->getValue('hpp_key'),
                    'isUSCountry' => $this->isUsCountry(),
                    'getOrderData' => $this->urlBuilder->getUrl(
                        'tonder/order/getorderdata'
                    ),
                    'getKeyData' => $this->urlBuilder->getUrl(
                        'tonder/key/getkeydata',
                        ['_secure'=> true]
                    ),
                    'isValid' => $this->checkStoreInfo(),
                    'isLoggedIn' => $this->isLoggedIn(),
                    'isVaultEnabled' => (int)$this->vaultConfig->getValue('active'),
                    'isEnable3dS' => (int)$this->isEnable3dS(),
                    'cardLookupUrl' => $this->urlBuilder->getUrl(
                        'tonder/order/cardlookup',
                        ['_secure' => true]
                    ),
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getStoreInfo()
    {
        return [
            $this->config->getValue('store_id'),
            $this->config->getValue('api_token')
        ];
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        $prefix = (bool)$this->config->getValue('sandbox_flag') ? 'sandbox_' : '';
        $after = $this->isUsCountry() ? '_us' : '';
        $path = $prefix . 'tonder_gateway' . $after;
        $gateway = $this->config->getValue($path);

        $apiRequest = 'tonder_path_servlet' . $after;
        $apiUrl = $this->config->getValue($apiRequest);

        return trim($gateway) . $apiUrl;
    }

    /**
     * @return string
     */
    public function getPaymentUrl()
    {
        $prefix = (bool)$this->config->getValue('sandbox_flag') ? 'sandbox_' : '';
        $after = $this->isUsCountry() ? '_us' : '';
        $path = $prefix . 'tonder_gateway' . $after;
        $gateway = $this->config->getValue($path);
        $preload_request = 'tonder_path_preload_request' . $after;
        $additionalPath = $this->config->getValue($preload_request);
        return trim($gateway) . $additionalPath;
    }

    /**
     * @return bool
     */
    public function checkStoreInfo()
    {
        $httpHeaders = new \Zend\Http\Headers();
        $httpHeaders->addHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);

        $request = new \Zend\Http\Request();
        $request->setHeaders($httpHeaders);
        $request->setUri($this->getPaymentUrl());
        $request->setMethod(\Zend\Http\Request::METHOD_POST);

        $params = new \Zend\Stdlib\Parameters([
            'hpp_id' => $this->config->getValue('hpp_id'),
            'hpp_key' => $this->config->getValue('hpp_key')
        ]);
        $request->setQuery($params);

        $client = new \Zend\Http\Client();
        $options = [
            'adapter'   => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            'maxredirects' => 0,
            'timeout' => 30
        ];
        $client->setOptions($options);
        try {
            $response = $client->send($request);
        } catch (\Exception $exception) {
            return false;
        }
        if ($response->getContent() == 'failed' || $response->getContent() == 'Invalid store credentials.') {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isUsCountry()
    {
        if ($this->config->getValue('environment') == Environment::ENVIRONMENT_US) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isEnable3dS()
    {
        if ($this->config->getValue('three_d_secure')) {
            return true;
        }

        return false;
    }
}
