<?php

namespace Tonder\Payment\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\HTTP\Client\Curl;
use Tonder\Payment\Helper\Data;
use Tonder\Payment\Logger\Logger;
use Magento\Sales\Api\OrderManagementInterface;
use Tonder\Payment\Model\TonderFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransactionCharge extends \Magento\Framework\App\Action\Action
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
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var TonderFactory
     */
    protected $tonderFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Construct method
     *
     * @param                                          Context                  $context
     * @param                                          JsonFactory              $resultJsonFactory
     * @param                                          StoreManagerInterface    $storeManager
     * @param                                          Session                  $checkoutSession
     * @param                                          Cart                     $cart
     * @param                                          ScopeConfigInterface     $scopeConfig
     * @param                                          Curl                     $curl
     * @param                                          Data                     $helper
     * @param                                          Logger                   $logger
     * @param                                          OrderFactory             $orderFactory
     * @param                                          OrderManagementInterface $orderManagement
     * @param                                          TonderFactory          $tonderFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        OrderFactory $orderFactory,
        OrderManagementInterface $orderManagement,
        TonderFactory $tonderFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->curl = $curl;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->orderManagement = $orderManagement;
        $this->tonderFactory = $tonderFactory;
        parent::__construct($context);
    }

    /**
     * Transaction charge action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam("orderId");
        $transactionInfo = $this->getRequest()->getParam("transactionInfo");
        $redirectUrl = "failed";
        if (isset($transactionInfo["id"])) {
            $publicKey = $this->helper->getPublicKey();
            $apiBaseUrl = $this->helper->getApiBaseUrl();
            $antiFraudMeta = $this->getRequest()->getParam("anti_fraud_metadata");
            $userLanguage = $this->getRequest()->getParam("tonder_user_language");
            $widgetVersion = $this->helper->getWidgetVersion();
            $transactionUrl = $apiBaseUrl."/transaction/charge/".$transactionInfo["id"];

            $this->curl->addHeader("X-Api-Client-Key", $publicKey);
            $this->curl->addHeader("X-Cash-Anti-Fraud-Metadata", $antiFraudMeta);
            if ($userLanguage && !empty($userLanguage)) {
                $this->curl->addHeader("X-Cash-Preferred-Locale", $userLanguage);
            }
            $this->curl->addHeader("X-Cash-Checkout-Widget-Version", $widgetVersion);
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->get($transactionUrl);
            $response = $this->curl->getBody();
            $response = json_decode($response, true);
            $additionalDetails = $response["status_details"]["detail"]["additional_details"];
            $chargeStatus = false;

            foreach ($additionalDetails as $additionalDetail) {
                if ($additionalDetail["name"]=="current_status") {
                    $chargeStatus = $additionalDetail["data"];
                }
            }
            if ($chargeStatus=="pending" || $chargeStatus=="pending_capture" || $chargeStatus=="captured" || $chargeStatus=="completed") {
                $redirectUrl = "success";
            }
        }
        if ($redirectUrl=="failed") {
            $order = $this->orderFactory->create()->load($orderId, 'tonder_order_id');
            if ($order->getId()) {
                if ($order->getStatus()!="canceled") {
                    $order->setStatus("canceled");
                    $order->setState("canceled");
                    $order->save();
                    $this->orderManagement->cancel($order->getId());
                }
                $this->checkoutSession->restoreQuote();
            }
        }

        $createChargeResponce = ["redirect"=>$redirectUrl];
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($createChargeResponce);
    }
}
