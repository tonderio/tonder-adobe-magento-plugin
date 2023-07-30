<?php

declare(strict_types=1);

namespace Tonder\Payment\Helper;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Encryption\EncryptorInterface;
use Tonder\Payment\Model\Adminhtml\Source\Mode;
use Tonder\Payment\Logger\Logger;
use Tonder\Payment\Helper\Curl as CurlTonderHelper;

class Data extends AbstractHelper
{
    const URL_LIVE = 'https://live.api.tonder.mx/v2';

    const URL_SANBOX = "https://sandbox.api.tonder.mx/v2";

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var CurlTonderHelper
     */
    protected $curlCustom;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        EncryptorInterface $encryptor,
        CountryFactory $countryFactory,
        Cart $cart,
        Session $checkoutSession,
        Context $context,
        ConfigInterface $config,
        CurlTonderHelper $curlCustom,
        Logger $logger
    ) {
        $this->encryptor = $encryptor;
        $this->countryFactory = $countryFactory;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->curlCustom = $curlCustom;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get webhook key
     *
     * @return string
     */
    public function webhookKey()
    {
        $mode = $this->getActiveMode();
        return $this->encryptor->decrypt(
            $this->scopeConfig->getValue('payment/tonder/' . $mode . '_webhook', ScopeInterface::SCOPE_STORE)
        );
    }

    /**
     * Get secret key
     *
     * @return string
     */
    public function getSecretKey()
    {
        $mode = $this->getActiveMode();
        return $this->encryptor->decrypt(
            $this->scopeConfig->getValue('payment/tonder/' . $mode . '_secret', ScopeInterface::SCOPE_STORE)
        );
    }

    /**
     * Get public key for payment
     *
     * @return mixed
     */
    public function getPublicKey()
    {
        $mode = $this->getActiveMode();
        return $this->scopeConfig->getValue('payment/tonder/' . $mode . '_api_key', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Is debug mode enabled
     *
     * @return                                 bool
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function debugMode()
    {
        $mode = $this->getActiveMode();
        $debug = false;
        if ($mode == "sandbox") {
            $tmpDebug = $this->scopeConfig->getValue(
                'payment/tonder/debug',
                ScopeInterface::SCOPE_STORE
            );
            if ($tmpDebug == "1") {
                $debug = true;
            } else {
                $debug = false;
            }
        }
        return $debug;
    }

    /**
     * Get active tonder mode
     *
     * @return                                 string
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getActiveMode()
    {
        $mode = $this->scopeConfig->getValue('payment/tonder/mode', ScopeInterface::SCOPE_STORE);
        if ($mode == Mode::PRODUCTION) {
            return 'production';
        } elseif ($mode == Mode::SANDBOX) {
            return 'sandbox';
        } else {
            return 'playground';
        }
    }

    /**
     * Get api base url
     *
     * @return                                 string
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getApiBaseUrl()
    {
        $mode = $this->getActiveMode();
        if ($mode == "production") {
            return self::URL_LIVE;
        } else {
            return self::URL_SANBOX;
        }
    }

    /**
     * Get widget version
     *
     * @return string
     */
    public function getWidgetVersion()
    {
        return 'plugin.magento/1.0';
    }

    /**
     * Tonder order update
     *
     * @param  string $tonderOrderId
     * @param  string $antiFraudMeta
     * @return void
     */
    public function tonderOrderUpdate($tonderOrderId, $antiFraudMeta)
    {
        $quote = $this->cart->getQuote();
        $quoteItems = $quote->getAllVisibleItems();
        $currencyCode = $quote->getStoreCurrencyCode();
        $publicKey = $this->getPublicKey();
        $apiBaseUrl = $this->getApiBaseUrl();
        $debugMode = $this->debugMode();
        $widgetVersion = $this->getWidgetVersion();

        $items = [];
        if ($quoteItems) {
            foreach ($quoteItems as $quoteItem) {
                $items[] = [
                    "item_total_amount" => [
                        "amount" => (int)($quoteItem->getRowTotal() * 100), "currency_code" => $currencyCode
                    ],
                    "description" => $quoteItem->getName(),
                    "id" => $quoteItem->getSku(),
                    "quantity" => $quoteItem->getQty(),
                    "unit_amount" => [
                        "amount" => (int)($quoteItem->getPrice() * 100),
                        "currency_code" => $currencyCode
                    ]
                ];
            }
        }

        $URL = $apiBaseUrl . '/order/' . $tonderOrderId;

        $postArray = [
            "order_total_amount" => [
                "amount" => (int)($quote->getGrandTotal() * 100), "currency_code" => $currencyCode
            ],
            "description" => "Tonder payment order",
            "purchases" => $items
        ];
        if ($debugMode) {
            $this->logger->info("-----Helper Update Order starts-----");
            $this->logger->info("Posted Data:");
        }

        $jsonData = json_encode($postArray);
        if ($debugMode) {
            $this->logger->info($jsonData);
        }
        $this->curlCustom->addHeader("X-Api-Client-Key", $publicKey);
        $this->curlCustom->addHeader("X-Cash-Anti-Fraud-Metadata", $antiFraudMeta);
        $this->curlCustom->addHeader("X-Cash-Checkout-Widget-Version", $widgetVersion);
        $this->curlCustom->addHeader("Content-Type", "application/json");
        $this->curlCustom->patch($URL, $jsonData);

        $response = $this->curlCustom->getBody();
        if ($debugMode) {
            $this->logger->info("Received Data:");
            $this->logger->info($response);
            $this->logger->info("-----Helper Update Order ends-----");
        }
    }
    /**
     * Get country code
     *
     * @param  string $countryCode
     * @return string
     */
    public function getCountryName($countryCode)
    {
        return $this->countryFactory->create()->loadByCode($countryCode)->getName();
    }
    /**
     * Clear last order id
     *
     * @return void
     */
    public function clearTonderOrder()
    {
        $this->checkoutSession->unsTonderOrderId();
        $this->checkoutSession->unsAntiFraudMeta();
    }
    /**
     * Get currency code
     *
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        $quote = $this->cart->getQuote();
        return $quote->getStoreCurrencyCode();
    }
    /**
     * Get Payment method status
     *
     * @return string
     */
    public function getPaymentMethodStatus()
    {
        return $this->scopeConfig->getValue('payment/tonder/active', ScopeInterface::SCOPE_STORE);
    }

    /**
     * get boolean value to bypass signifyd rejected order
     *
     * @return boolean
     */
    public function getProcessSignifydRejectedOrder()
    {
        return (bool) $this->scopeConfig->getValue(
            'payment/tonder/processSignifydRejectedOrder',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getToken()
    {
        return $this->encryptor->decrypt($this->config->getValue('token'));
    }
}
