<?php

namespace Tonder\Payment\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Tonder\Payment\Helper\Data;
use Tonder\Payment\Logger\Logger;
use Magento\Framework\App\Action\Action;
use Magento\Directory\Model\CountryFactory;

class CreateOrder extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param Session               $checkoutSession
     * @param Cart                  $cart
     * @param ScopeConfigInterface  $scopeConfig
     * @param Curl                  $curl
     * @param Data                  $helper
     * @param Logger                $logger
     * @param CountryFactory        $countryFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        Cart $cart,
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        Data $helper,
        Logger $logger,
        CountryFactory $countryFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->curl = $curl;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->countryFactory = $countryFactory;
        parent::__construct($context);
    }

    /**
     * Create order action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $debugMode = $this->helper->debugMode();
        $quote = $this->cart->getQuote();
        $quoteItems = $quote->getAllVisibleItems();
        $currencyCode = $quote->getStoreCurrencyCode();

        $publicKey = $this->helper->getPublicKey();
        $apiBaseUrl = $this->helper->getApiBaseUrl();
        $antiFraudMeta = $this->getRequest()->getParam("anti_fraud_metadata");
        $widgetVersion = $this->helper->getWidgetVersion();
        
        $userInfo = ["user_info"=>[]];
        $customerEmail = $this->getRequest()->getParam("customer_email");

        if (!empty($quote->getCustomerEmail())) {
            $customerEmail = $quote->getCustomerEmail();
        }
        if (!$customerEmail && $quote->getBillingAddress()->getEmail()) {
            $customerEmail = $quote->getBillingAddress()->getEmail();
        }

        $consumerDetails = [
            "contact"=>["email"=>$customerEmail]
        ];

        $items = [];
        if ($quoteItems) {
            foreach ($quoteItems as $quoteItem) {
                $items[] = [
                    "item_total_amount"=>[
                        "amount"=>(int)($quoteItem->getRowTotal()*100),
                        "currency_code"=>$currencyCode
                    ],
                    "description"=> $quoteItem->getName(),
                    "id"=> "{$quoteItem->getSku()}",
                    "quantity"=> (int)$quoteItem->getQty(),
                    "unit_amount"=>[
                        "amount"=> (int)($quoteItem->getPrice()*100),
                        "currency_code"=> $currencyCode
                    ]
                ];
            }
        }

        $URL = $apiBaseUrl.'/order';

        $storeTitle = $this->storeManager->getStore()->getGroup()->getName();
        $description = "Magento - ".$storeTitle." Quote #".$quote->getId();

        $postArray = [
            "order_total_amount" => [
                "amount"=>(int)($quote->getGrandTotal()*100),
                "currency_code"=>$currencyCode
            ],
            "description"=> $description,
            "purchases"=> $items,
            "consumer_details"=> $consumerDetails
        ];

        try {
            if ($debugMode) {
                $this->logger->info("-----Create Order starts-----");
                $this->logger->info("Quote ID: " . $quote->getId());
                $this->logger->info("Posted Data:");
            }

            $jsonData = json_encode($postArray);
            if ($debugMode) {
                $this->logger->info($jsonData);
            }
            $this->curl->addHeader("X-Api-Client-Key", $publicKey);
            $this->curl->addHeader("X-Cash-Anti-Fraud-Metadata", $antiFraudMeta);
            $this->curl->addHeader("X-Cash-Checkout-Widget-Version", $widgetVersion);
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->post($URL, $jsonData);

            $response = $this->curl->getBody();
            if ($debugMode) {
                $this->logger->info("Received Data:");
                $this->logger->info($response);
                $this->logger->info("-----Create Order ends-----");
            }
            
            $response = json_decode($response, true);

            $resultJson = $this->resultJsonFactory->create();

            if ($this->curl->getStatus()!=200) {
                $res = [
                    'message' => $response['message'],
                    'status' => $response['status']
                ];

                return $resultJson->setData($res)->setHttpResponseCode($this->curl->getStatus());
            } else {
                if (isset($response["order_information"])) {
                    if (isset($response["order_information"]["order_id"])) {
                        $this->checkoutSession->setTonderOrderId($response["order_information"]["order_id"]);
                        $this->checkoutSession->setAntiFraudMeta($antiFraudMeta);
                    }
                }
                $userInfo = json_encode($userInfo);
                $userInfo = json_decode($userInfo, true);

                $createOrderResponse = array_merge($response, $userInfo);
            }
            
            return $resultJson->setData($createOrderResponse);

        } catch (\Exception $e) {
            $this->logger->error(__('Create order failed:-----'));
            $this->logger->error('ERROR', ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            if ($e->getMessage()) {
                throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
            } else {
                throw new \Magento\Framework\Validator\Exception(__('Could not create order'));
            }
        }
    }
}
