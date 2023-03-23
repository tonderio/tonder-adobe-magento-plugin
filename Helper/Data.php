<?php

namespace Tonder\Payment\Helper;

use Tonder\Payment\Model\Adminhtml\Source\Mode;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $quoteSender;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var \Magento\Sales\Model\Service\CreditmemoService
     */
    protected $creditmemoService;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $invoiceModel;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Tonder\Payment\Model\Curl
     */
     protected $curlCustom;

     /**
      * @var \Tonder\Payment\Logger\Logger
      */
     protected $loggerCustom;

     /**
      * @var \Magento\Directory\Model\CountryFactory
      */
     protected $countryFactory;

     /**
      * @var \Magento\Checkout\Model\Cart
      */
     protected $cart;

     /**
      * @var \Magento\Checkout\Model\Session
      */
     protected $checkoutSession;

    /**
     * @param   \Magento\Framework\App\Request\Http                 $request
     * @param   \Magento\Framework\App\Response\Http                $response
     * @param   \Psr\Log\LoggerInterface                            $logger
     * @param   \Magento\Framework\Event\ManagerInterface           $eventManager
     * @param   \Magento\Sales\Model\Order\Email\Sender\OrderSender $quoteSender
     * @param   \Magento\Store\Model\StoreManagerInterface          $storeManager
     * @param   \Magento\Sales\Model\Order\CreditmemoFactory        $creditmemoFactory
     * @param   \Magento\Sales\Model\Service\CreditmemoService      $creditmemoService
     * @param   \Magento\Framework\DB\TransactionFactory            $transactionFactory
     * @param   \Magento\Sales\Model\Order\Invoice                  $invoiceModel
     * @param   \Magento\Framework\UrlInterface                     $urlInterface
     * @param   \Magento\Framework\Encryption\EncryptorInterface    $encryptor
     * @param   \Tonder\Payment\Model\Curl                        $curlCustom
     * @param   \Tonder\Payment\Logger\Logger                     $loggerCustom
     * @param   \Magento\Directory\Model\CountryFactory             $countryFactory
     * @param   \Magento\Checkout\Model\Cart                        $cart
     * @param   \Magento\Checkout\Model\Session                     $checkoutSession
     * @param   \Magento\Framework\App\Helper\Context               $context
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\Http $response,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $quoteSender,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Invoice $invoiceModel,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Tonder\Payment\Model\Curl $curlCustom,
        \Tonder\Payment\Logger\Logger $loggerCustom,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->orderSender = $quoteSender;
        $this->storeManager = $storeManager;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceModel = $invoiceModel;
        $this->urlInterface = $urlInterface;
        $this->encryptor = $encryptor;
        $this->curlCustom = $curlCustom;
        $this->loggerCustom = $loggerCustom;
        $this->countryFactory = $countryFactory;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
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
            $this->scopeConfig->getValue(
                'payment/tonder/'.$mode.'_webhook',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
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
            $this->scopeConfig->getValue(
                'payment/tonder/'.$mode.'_secret',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
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
        return $this->scopeConfig->getValue(
            'payment/tonder/'.$mode.'_api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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
        if ($mode=="sandbox") {
            $tmpDebug = $this->scopeConfig->getValue(
                'payment/tonder/debug',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($tmpDebug=="1") {
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
        $mode = $this->scopeConfig->getValue('payment/tonder/mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
        if ($mode=="production") {
            return 'https://live.api.tonder.mx/v2';
        } else {
            return 'https://sandbox.api.tonder.mx/v2';
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
                    "item_total_amount"=>[
                        "amount"=>(int)($quoteItem->getRowTotal()*100),"currency_code"=>$currencyCode
                    ],
                    "description"=> $quoteItem->getName(),
                    "id"=> $quoteItem->getSku(),
                    "quantity"=> $quoteItem->getQty(),
                    "unit_amount"=>[
                        "amount"=> (int)($quoteItem->getPrice()*100),
                        "currency_code"=> $currencyCode
                        ]
                    ];
            }
        }

        $URL = $apiBaseUrl.'/order/'.$tonderOrderId;

        $postArray = [
            "order_total_amount" => [
                "amount"=>(int)($quote->getGrandTotal()*100),"currency_code"=>$currencyCode
            ],
            "description"=> "Tonder payment order",
            "purchases"=>$items
        ];
        if ($debugMode) {
            $this->loggerCustom->info("-----Helper Update Order starts-----");
            $this->loggerCustom->info("Posted Data:");
        }

        $jsonData = json_encode($postArray);
        if ($debugMode) {
            $this->loggerCustom->info($jsonData);
        }
        $this->curlCustom->addHeader("X-Api-Client-Key", $publicKey);
        $this->curlCustom->addHeader("X-Cash-Anti-Fraud-Metadata", $antiFraudMeta);
        $this->curlCustom->addHeader("X-Cash-Checkout-Widget-Version", $widgetVersion);
        $this->curlCustom->addHeader("Content-Type", "application/json");
        $this->curlCustom->patch($URL, $jsonData);

        $response = $this->curlCustom->getBody();
        if ($debugMode) {
            $this->loggerCustom->info("Received Data:");
            $this->loggerCustom->info($response);
            $this->loggerCustom->info("-----Helper Update Order ends-----");
        }
    }
    /**
     * Get country code
     *
     * @param  string $countryCode
     * @return string
     */
    public function getCountryCode($countryCode)
    {
        if ($countryCode) {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            if ($country->getData('iso3_code')) {
                $countryCode = $country->getData('iso3_code');
            }
        }
        return $countryCode;
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
        return $this->scopeConfig->getValue(
            'payment/tonder/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get boolean value to bypass signifyd rejected order
     *
     * @return boolean
     */
    public function getProcessSignifydRejectedOrder()
    {
        return (boolean) $this->scopeConfig->getValue(
            'payment/tonder/processSignifydRejectedOrder',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
