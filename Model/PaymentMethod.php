<?php

namespace Tonder\Payment\Model;

use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    public const METHOD_CODE = 'tonder';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var int
     */
    protected $_minOrderTotal = 0;

    /**
     * @var string[]
     */
    protected $_supportedCurrencyCodes = ['MXN'];

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * @var \Tonder\Payment\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Tonder\Payment\Logger\Logger
     */
     protected $_logger;

    /**
     * @var ResourceModel\Tonder\Collection
     */
     protected $_tonderCollection;

    /**
     * @var TonderFactory
     */
     protected $_tonderFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
     protected $_cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
     protected $_cookieMetadataFactory;

    /**
     * @var Curl
     */
     protected $_curlCustom;

    /**
     * @var \Magento\Framework\Filesystem
     */
     protected $_filesystem;
    /**
     * @var CountryFactory
     */
     protected $_countryFactory;

     /**
      * @var CountryFactory
      */
     protected $_regionFactory;

     /**
      * @var \Magento\Store\Model\StoreManagerInterface
      */
     protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ResourceInterface
     */
    protected $moduleResource;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var ResourceInterface
     */
    protected $moduleManager;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $objectManager;


    /**
     * @param  \Magento\Framework\Model\Context                       $context
     * @param  \Magento\Framework\Registry                            $registry
     * @param  \Magento\Framework\Api\ExtensionAttributesFactory      $extensionFactory
     * @param  \Magento\Framework\Api\AttributeValueFactory           $customAttributeFactory
     * @param  \Magento\Payment\Helper\Data                           $paymentData
     * @param  \Magento\Framework\App\Config\ScopeConfigInterface     $scopeConfig
     * @param  \Magento\Payment\Model\Method\Logger                   $logger
     * @param  \Magento\Framework\HTTP\Client\Curl                    $curl
     * @param  \Tonder\Payment\Helper\Data                          $helper
     * @param  \Tonder\Payment\Logger\Logger                        $_logger
     * @param  TonderFactory                                        $tonderFactory
     * @param  ResourceModel\Tonder\Collection                      $tonderCollection
     * @param  \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param  \Magento\Framework\Stdlib\CookieManagerInterface       $cookieManager
     * @param  \Magento\Framework\Session\SessionManagerInterface     $sessionManager
     * @param  Curl                                                   $curlCustom
     * @param  \Magento\Framework\Filesystem                          $filesystem
     * @param  \Magento\Directory\Model\CountryFactory                $countryFactory
     * @param  \Magento\Store\Model\StoreManagerInterface             $storeManager,
     * @param  \Magento\Customer\Model\Session                        $session
     * @param  \Magento\Framework\Module\ResourceInterface            $moduleResource
     * @param  \Magento\Directory\Model\RegionFactory                 $regionFactory
     * @param  \Magento\Framework\Module\Manager                      $moduleManager
     * @param  \Magento\Framework\ObjectManagerInterface              $objectManager
     * @param  array                                                  $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Tonder\Payment\Helper\Data $helper,
        \Tonder\Payment\Logger\Logger $_logger,
        \Tonder\Payment\Model\TonderFactory $tonderFactory,
        \Tonder\Payment\Model\ResourceModel\Tonder\Collection $tonderCollection,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Tonder\Payment\Model\Curl $curlCustom,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Module\ResourceInterface $moduleResource,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Module\Manager            $moduleManager,
        \Magento\Framework\ObjectManagerInterface    $objectManager,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
        $this->_curl = $curl;
        $this->_minOrderTotal = 0;
        $this->_helper = $helper;
        $this->_logger = $_logger;
        $this->_tonderFactory = $tonderFactory;
        $this->_tonderCollection = $tonderCollection;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->_customerSession = $session;
        $this->_curlCustom = $curlCustom;
        $this->_filesystem = $filesystem;
        $this->_countryFactory = $countryFactory;
        $this->_regionFactory = $regionFactory;
        $this->_storeManager = $storeManager;
        $this->moduleResource = $moduleResource;
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Validate the supported currency
     *
     * @param  string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    /**
     * Capture payment
     *
     * @param                                         \Magento\Payment\Model\InfoInterface $payment
     * @param                                         float                                $amount
     * @return                                        $this|PaymentMethod
     * @throws                                        \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();

        $tonder = $this->_tonderFactory->create()->load($order->getTonderOrderId(), 'tonder_order_id');
        
        if ($tonder->getInvoiceStatus()==1 || $tonder->getInvoiceStatus()==3) {
            return $this;
        } else {
            $tonder->setInvoiceStatus(2)->setOrderId($order->getId())->save();
        }

        $transactionId = $order->getTonderTransactionId();
        $secretKey = $this->_helper->getSecretKey();
        $currencyCode = $order->getStoreCurrencyCode();
        $apiBaseUrl = $this->_helper->getApiBaseUrl();
        $debugMode = $this->_helper->debugMode();
        $widgetVersion = $this->_helper->getWidgetVersion();
        $URL = $apiBaseUrl."/transaction/capture/".$transactionId;
        $postArray = ["amount"=>(int)($amount*100),"currency_code"=>$currencyCode];

        $jsonData = json_encode($postArray);
        if ($debugMode) {
            $this->_logger->info("-----Capture amount starts-----");
            $this->_logger->info("Posted Data:");
            $this->_logger->info($jsonData);
        }
        $this->_curl->addHeader("X-Api-Client-Key", $secretKey);
        $this->_curl->addHeader("X-Cash-Checkout-Widget-Version", $widgetVersion);
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->post($URL, $jsonData);
        $response = $this->_curl->getBody();
        if ($debugMode) {
            $this->_logger->info("Received Data:");
            $this->_logger->info($response);
            $this->_logger->info("-----Capture amount ends-----");
        }
        $response = json_decode($response, true);

        $status = false;
        if ($this->_curl->getStatus()==200) {
            if (isset($response["status_details"])) {
                if (isset($response["status_details"]["status"])) {
                    $status = $response["status_details"]["status"];
                }
            }
        } else {
            if ($status==false) {
                $status = $response["status"];
            }
        }
        try {
            if ($status!="success") {
                $tonder = $this->_tonderFactory->create()->load($order->getTonderOrderId(), 'tonder_order_id');
                $tonder->setInvoiceStatus(0)->save();
            }
            if ($status=="success") {
                $payment->setTransactionId($transactionId)->setIsTransactionClosed(0);
                return $this;
            } elseif ($status=="failure") {
                throw new LocalizedException(
                    __('Tonder error: '. $response["detail"]["message"])
                );
            } else {
                throw new LocalizedException(
                    __('Payment capturing error.')
                );
            }
        } catch (LocalizedException $e) {
            throw new \Exception(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
        return $this;
    }

    /**
     * Initiate refund
     *
     * @param                                        InfoInterface $payment
     * @param                                        float         $amount
     * @return                                       $this|PaymentMethod
     * @throws                                       \Magento\Framework\Validator\Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $transactionId = $order->getTonderTransactionId();
        $secretKey = $this->_helper->getSecretKey();
        $currencyCode = $order->getStoreCurrencyCode();
        $apiBaseUrl = $this->_helper->getApiBaseUrl();
        $debugMode = $this->_helper->debugMode();
        $widgetVersion = $this->_helper->getWidgetVersion();
        $URL = $apiBaseUrl."/transaction/refund/".$transactionId;
        $postArray = ["amount"=>(int)($amount*100),"currency_code"=>$currencyCode];

        $tonder = $this->_tonderFactory->create()->load($order->getTonderOrderId(), 'tonder_order_id');
        $tonder->setOrderId($order->getId());
        $tonder->setTonderOrderId($order->getTonderOrderId());
        $refundStatus = $tonder->getRefundStatus();
        if ($refundStatus==2) {
            $payment->setTransactionId(
                $transactionId . '-' .
                \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND.'_portal'
            )
                ->setParentTransactionId($transactionId)
                ->setIsTransactionClosed(1)
                ->setShouldCloseParentTransaction(1);
            return $this;
        }

        $tonder->setRefundStatus(1)->save();

        $directory = $this->_filesystem->getDirectoryWrite(
            DirectoryList::TMP
        );
        $tmpFileName = $directory->getAbsolutePath(
            'tonder_refund_'.$order->getId()
        );
        $directory->create($tmpFileName);

        $jsonData = json_encode($postArray);
        if ($debugMode) {
            $this->_logger->info("-----Refund starts-----");
            $this->_logger->info("Posted Data:");
            $this->_logger->info($jsonData);
        }
        $this->_curl->addHeader("X-Api-Client-Key", $secretKey);

        $this->_curl->addHeader("X-Cash-Checkout-Widget-Version", $widgetVersion);
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->post($URL, $jsonData);
        $response = $this->_curl->getBody();
        if ($debugMode) {
            $this->_logger->info("Received Data:");
            $this->_logger->info($response);
            $this->_logger->info("-----Refund ends-----");
        }
        $response = json_decode($response, true);

        $status = false;
        if ($this->_curl->getStatus()==200) {
            if (isset($response["status_details"])) {
                if (isset($response["status_details"]["status"])) {
                    $status = $response["status_details"]["status"];
                }
            }
        } else {
            if ($status==false) {
                $status = $response["status"];
            }
        }
        $errorMessage = 'Unable to create creditmemo';
        try {
            if ($status!="success") {
                $tonder = $this->_tonderFactory->create()->load($order->getTonderOrderId(), 'tonder_order_id');
                $tonder->setRefundStatus(0)->save();
            }
            if ($status=="success") {
                $payment->setTransactionId(
                    $transactionId . '-' .
                    \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND.'_magento'
                )
                    ->setParentTransactionId($transactionId)
                    ->setIsTransactionClosed(1)
                    ->setShouldCloseParentTransaction(1);
            } elseif ($status=="failure") {
                $errorMessage = 'Tonder error: '. $response["detail"]["message"];
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($errorMessage)
                );
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($errorMessage)
                );
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($errorMessage));
        }
        return $this;
    }

    /**
     * Check if method is available for current quote
     *
     * @param                                         \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return                                        bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;
    }

    /**
     * Authorize/Capture Payment
     *
     * @param                                         \Magento\Payment\Model\InfoInterface $payment
     * @param                                         float                                $amount
     * @return                                        $this|PaymentMethod
     * @throws                                        \Magento\Framework\Validator\Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $publicKey = $this->_helper->getPublicKey();
        $secretKey = $this->_helper->getSecretKey();
        $apiBaseUrl = $this->_helper->getApiBaseUrl();
        $debugMode = $this->_helper->debugMode();

        $additionalInformation = $payment->getAdditionalInformation();

        if ($debugMode) {
            $this->_logger->info("-----Payment Initiated-----");
            
            $this->_logger->info("Received Additional Data in Checkout request:");
            $this->_logger->info(json_encode($additionalInformation));
        }
        
        $antiFraudMeta = "";
        $userLanguage = $this->parseLanguage($additionalInformation['tonder_user_language'] ?? '');
        if (isset($additionalInformation['anti_fraud_metadata'])) {
            $antiFraudMeta = $additionalInformation['anti_fraud_metadata'];
        }

        $order = $payment->getOrder();
        
        if ($debugMode) {
            $this->_logger->info("Order update Started with -- ");
            $this->_logger->info(json_encode(["order" => $order, "antiFraudMeta" => $antiFraudMeta, "order_id" => $additionalInformation['order_id']]));
        }
        $this->tonderOrderUpdate($order, $antiFraudMeta, $additionalInformation['order_id']);

        $widgetVersion = $this->_helper->getWidgetVersion();

        if ($debugMode) {
            $this->_logger->info("Order update Ends --");
            $this->_logger->info("Merchant settings api initiated with --");
            $this->_logger->info(json_encode(["headers" => [
                "X-Cash-Anti-Fraud-Metadata" => $antiFraudMeta,
                "X-Cash-Checkout-Widget-Version" => $antiFraudMeta,
                "X-Cash-Preferred-Locale" => $userLanguage
            ]]));
        }

        $merchantUrl = $apiBaseUrl."/merchant/setting/checkout-widget";
        $this->_curl->addHeader("X-Api-Client-Key", $publicKey);
        $this->_curl->addHeader("X-Cash-Anti-Fraud-Metadata", $antiFraudMeta);
        $this->_curl->addHeader("X-Cash-Preferred-Locale", $userLanguage);
        $this->_curl->addHeader("X-Cash-Checkout-Widget-Version", $widgetVersion);
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->get($merchantUrl);
        $response = $this->_curl->getBody();

        if ($debugMode) {
            $this->_logger->info("response data --");
            $this->_logger->info($response);
            $this->_logger->info("Merchant settings api initiated ends --");
        }

        $response = json_decode($response, true);
        $merchantConfig = $response["configuration_details"];
        $autoCapture = false;
        foreach ($merchantConfig as $key => $config) {
            if ($config["name"]=="auto_capture" && $config["data"]=="true") {
                $autoCapture = true;
            }
        }

        try {
            $tonder = $this->_tonderFactory->create()->load(
                $additionalInformation['order_id'],
                'tonder_order_id'
            );
            $tonder->setTonderOrderId($additionalInformation['order_id']);
            $tonder->setTonderCharge(1);
            $tonder->setInvoiceStatus(1)->save();

            $currencyCode = $order->getStoreCurrencyCode();
            $customerEmail = $order->getCustomerEmail();

            $URL = $apiBaseUrl.'/transaction/charge';

            $storeTitle = $this->_storeManager->getStore()->getGroup()->getName();
            $orderIncrementId = $order->getIncrementId();
            $description = "Magento - ".$storeTitle." Order #".$orderIncrementId;

            $purchaseDetails = [];
            if ($additionalInformation['order_id']!="") {
                $purchaseDetails = [
                    "tonder_system_order_id"=>(string)$additionalInformation['order_id'],
                    "external_system_order_id"=>(string)$orderIncrementId
                ];
            }

            $postArray = [
                "amount_details" => [
                    "amount"=>(int)($order->getGrandTotal()*100),
                    "currency_code"=>$currencyCode
                ],
                "description"=> $description,
                "consumer_details" => [
                    "contact"=>[
                        "email"=>$customerEmail
                    ],
                    "name"=> $this->getCustomerNameObject($order, false)
                ],
                "processing_instructions"=> [
                    "auto_capture"=>$autoCapture,
                    "use_order_payment_detail"=>true
                ],
                "purchase_details"=>$purchaseDetails
            ];

            // Prepare Additional Details - STARTS
            $chargeAdditionalDetails = [];

            if (isset($additionalInformation['charge_additional_details'])) {
                $additionalDetails = $additionalInformation['charge_additional_details'];
                if ($additionalDetails!="" && $additionalDetails!=null) {
                    $chargeAdditionalDetails = $this->dataStringify(json_decode($additionalDetails));
                }
            }
            $is_store_pickup = false;
            $is_registered_client = false;

            foreach ($chargeAdditionalDetails as $key => $additional_detail) {
                if ($additional_detail['name'] === "is_store_pickup") {
                    $is_store_pickup = true;
                    $chargeAdditionalDetails[$key]['data'] = boolval($chargeAdditionalDetails[$key]['data']);
                }
                if ($additional_detail['name'] === "is_registered_client") {
                    $is_registered_client = true;
                    $chargeAdditionalDetails[$key]['data'] = boolval($chargeAdditionalDetails[$key]['data']);
                }
                if ($additional_detail['name'] === "in_blacklist") {
                    $chargeAdditionalDetails[$key]['data'] = boolval($chargeAdditionalDetails[$key]['data']);
                }
            }

            if (!$is_store_pickup) {
                $chargeAdditionalDetails[] = [
                    "name" => "is_store_pickup",
                    "data" => $this->identifyStorePickup($order),
                ];
            }

            if (!$is_registered_client) {
                $chargeAdditionalDetails[] = [
                    "name" => "is_registered_client",
                    "data" => $this->_customerSession->isLoggedIn(),
                ];
            }

            if ($this->moduleResource->getDbVersion('Tonder_Payment')) {
                $chargeAdditionalDetails[] = [
                    "name" => "plugin_version",
                    "data" => $this->moduleResource->getDbVersion('Tonder_Payment'),
                ];
            }

            /**
             * this condition is used to check if magento have signifyd connect module added and enabled,
             * if true, then we collect signifyd case id, score and guarantee label to be shared along with
             * create charge data in additional details
             */
            if ($this->moduleManager->isEnabled("Signifyd_Connect")) {
                $casedataResourceModel = $this->objectManager->get("\Signifyd\Connect\Model\ResourceModel\Casedata");
                $casedataFactory = $this->objectManager->get("\Signifyd\Connect\Model\CasedataFactory");

                $case = $casedataFactory->create();
                $casedataResourceModel->load($case, $order->getQuoteId(), 'quote_id');

                if ($case->isEmpty() === false) {
                    if ($case->getGuarantee() == "ACCEPT") {
                        $labelGuarantee = 'APPROVED';
                    } elseif ($case->getGuarantee() == "REJECT") {
                        $labelGuarantee = 'DECLINED';
                    } else {
                        $labelGuarantee = $case->getGuarantee();
                    }

                    $chargeAdditionalDetails[] = [
                        "name" => "signifyd_case_id",
                        "data" => strval($case->getCode()),
                    ];
                    $chargeAdditionalDetails[] = [
                        "name" => "signifyd_guarantee",
                        "data" => strval($labelGuarantee),
                    ];
                    $chargeAdditionalDetails[] = [
                        "name" => "signifyd_score",
                        "data" => strval($case->getScore()),
                    ];
                }
            }

            $postArray["additional_details"] = [
                "details"=>$this->dataStringify($chargeAdditionalDetails)
            ];

            // Prepare Additional Details - ENDS
            $jsonData = json_encode($postArray);

            if ($debugMode) {
                $this->_logger->info("-----Create Charge starts-----");

                $this->_logger->info(json_encode(["headers" => [
                    "X-Cash-Anti-Fraud-Metadata" => $antiFraudMeta,
                    "X-Cash-Checkout-Widget-Version" => $antiFraudMeta,
                    "X-Cash-Preferred-Locale" => $userLanguage
                ]]));

                $this->_logger->info("Posted Data:");
                $this->_logger->info($jsonData);
            }
            
            $this->_curl->addHeader("X-Api-Client-Key", $secretKey);
            $this->_curl->addHeader("X-Cash-Anti-Fraud-Metadata", $antiFraudMeta);
            $this->_curl->addHeader("X-Cash-Preferred-Locale", $userLanguage);
            $this->_curl->addHeader("X-Cash-Checkout-Widget-Version", $widgetVersion);
            $this->_curl->addHeader("Content-Type", "application/json");
            $this->_curl->post($URL, $jsonData);
            $response = $this->_curl->getBody();

            if (isset($additionalInformation['cardExpiryMonth'])) {
                $payment->setCcExpYear(
                    $additionalInformation['cardExpiryMonth']
                );
            }
            if (isset($additionalInformation['cardExpiryYear'])) {
                $payment->setCcExpYear(
                    $additionalInformation['cardExpiryMonth']
                );
            }
            if (isset($additionalInformation['cardLast4'])) {
                $payment->setCcLast4(
                    $additionalInformation['cardLast4']
                );
            }
            if (isset($additionalInformation['cardBin'])) {
                $payment->setAdditionalInformation(
                    'cc_number',
                    $additionalInformation['cardBin']
                );
            }
            if (isset($additionalInformation['order_id'])) {
                $order->setExtOrderId($additionalInformation['order_id']);
            }

            if ($debugMode) {
                $this->_logger->info("Received Data:");
                $this->_logger->info($response);
                $this->_logger->info("-----Create charge ends-----");
            }
            $response = json_decode($response, true);
            $status = false;
            
            $this->updateCharge($additionalInformation['order_id']);
            
            // added a check if curl unable to initiate that will help us throw and handle error
            if (!is_array($response)) {
                throw new \Magento\Framework\Validator\Exception(__(json_encode($response)));
            }

            if ($this->_curl->getStatus()==400) {
                if ($response["status"]=="failure") {
                    $code = $response["detail"]["code"] ?? '';
                    if ($code == "order_already_associated_with_charge") {
                        $this->setOrderExist(2);
                        throw new \Magento\Framework\Validator\Exception(__($this->error($response)));
                    }
                }
            }
            if ($this->_curl->getStatus()==200) {
                $status = $response["status_details"]["status"] ?? false;
            } elseif ($status==false) {
                $status = $response["status"] ?? false;
            }
            $instructions = '<span class="sub-title" '.
            'style="font-size: 1.4rem; font-weight: 600; width: 100%;float: left;">'.
            'Payment method Title</span>';
            $instructions = '';
            if ($status!="success") {
                $tonder = $this->_tonderFactory->create()->load(
                    $additionalInformation['order_id'],
                    'tonder_order_id'
                );
                $tonder->setTonderOrderId($additionalInformation['order_id']);
                $tonder->setInvoiceStatus(0)->save();
            }
            $transactionId = '';
            $verificationRequired = false;

            if ($status=="success") {
                $successAttr = $response["status_details"]["detail"]["additional_details"];
                $chargeStatus = "";
                foreach ($successAttr as $key => $val) {
                    if ($val["name"]=="charge_status") {
                        $chargeStatus = $val["data"];
                    }
                    if ($val["name"] === "cash_transaction") {
                        $payment->setTransactionId($val["data"]);
                    }
                }
                if ($chargeStatus=="captured") {
                    $status = "success";
                } else {
                    $tonder = $this->_tonderFactory->create()->load(
                        $additionalInformation['order_id'],
                        'tonder_order_id'
                    );
                    $tonder->setTonderOrderId($additionalInformation['order_id']);
                    $tonder->setInvoiceStatus(0)->save();

                    $status = "pending";
                    $payment->setIsTransactionPending(true);
                }
                $order->setTonderOrderId($additionalInformation['order_id']);
                if ($autoCapture) {
                    $order->setTonderStatus($status);
                } else {
                    $order->setTonderStatus("pending_capture");
                }
                $order->setTonderTransactionId($response["id"]);
                $payment->setAdditionalInformation(
                    'instructions',
                    $instructions
                );
                return $this->_placeOrder($payment, $amount);
            } elseif ($status=="failure") {
                throw new \Magento\Framework\Validator\Exception(__($this->error($response)));
            } elseif ($status=="pending") {
                $pendingUrl = '';
                $enableInstruction = false;
                $pendingAttr = $response["detail"]["additional_details"];
                foreach ($pendingAttr as $key => $val) {
                    if ($val["name"]=="cash_transaction") {
                        $payment->setTransactionId($val["data"]);

                        $instructions .= '<span class="store_bank_details"><b>Transaction ID: </b>'.
                        $val["data"].'</span>';
                    }
                    if ($val["name"]=="transaction_clabe") {
                        $instructions .= '<span class="store_bank_details"><b>CLABE: </b>'.
                        $val["data"].'</span>';
                    }
                    if ($val["name"]=="bank_name") {
                        $instructions .= '<span class="store_bank_details"><b>Banco de destino: </b>'.
                        $val["data"].'</span>';
                    }
                    if ($val["name"]=="payment_expiry_formatted") {
                        $instructions .= '<span class="store_bank_details"><b>Fecha de expiración: </b>'.
                        $val["data"].'</span>';
                        $enableInstruction = true;
                    }
                    if ($val["name"]=="reference_number") {
                        $instructions .= '<span class="store_bank_details"><b>Referencia de transacción: </b>'.
                        $val["data"].'</span>';
                    }
                    if ($val["name"]=="reference_url") {
                        $instructions .= '<span class="store_bank_details" '.
                        'style="line-height: 55px;vertical-align: middle;height: 55px;">'.
                        '<b style="float: left;">Código de barras: </b><img src="'.
                        $val["data"].'" style="float: left;margin-left: 10px;"></span>';
                    }
                }
                foreach ($pendingAttr as $key => $val) {
                    if ($val["name"]=="redirect_url") {
                        $pendingUrl = $val["data"];
                        $this->setVerificationLink($pendingUrl);
                        $verificationRequired = true;

                    }
                    if ($val["name"]=="cash_transaction") {
                        $transactionId = $val["data"];
                    }
                    if ($val["name"]=="payment_instructions") {
                        $instructions .= '<span class="store_bank_details">'.
                        '<b>Instrucciones: </b></span>';
                        $paymentInstructions = $val["data"];
                        foreach ($paymentInstructions as $paymentInstruction) {
                            $instructions .= $paymentInstruction["value"];
                        }
                    }
                }

                if (!$autoCapture && $verificationRequired) {
                    $order->setTonderStatus("pending_capture");
                } else {
                    $order->setTonderStatus($status);
                }

                $order->setTonderValidationUrl($pendingUrl);
                $order->setTonderOrderId($additionalInformation['order_id']);
                $order->setTonderTransactionId($transactionId);

                $payment->setIsTransactionPending(true);

                if ($enableInstruction) {
                    $payment->setAdditionalInformation(
                        'instructions',
                        $instructions
                    );
                }

                return $this->_placeOrder($payment, $amount);
            } elseif ($status=="cancelled") {
                $order->setTonderOrderId($additionalInformation['order_id']);
                $order->setTonderTransactionId($response["id"]);
                $order->setTonderStatus($status);
                $payment->setAdditionalInformation(
                    'instructions',
                    $instructions
                );
                return $this->_placeOrder($payment, $amount);
            } else {
                throw new \Magento\Framework\Validator\Exception(__($this->error($response)));
            }
        } catch (\Exception $e) {
            $this->_logger->error(__('Payment capturing error.'));
            $this->_logger->error('ERROR', ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            if ($e->getMessage()) {
                throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
            } else {
                throw new \Magento\Framework\Validator\Exception(__('Could not create charge'));
            }
        }
    }

    /**
     * Place order
     *
     * @param                                         Payment $payment
     * @param                                         float   $amount
     * @return                                        $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _placeOrder(Payment $payment, $amount)
    {
        $this->_helper->clearTonderOrder();
        return $this;
    }

    /**
     * Set 3ds verification link
     *
     * @param                                         string $value
     * @param                                         int    $duration
     * @return                                        void
     * @throws                                        \Magento\Framework\Exception\InputException
     * @throws                                        \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws                                        \Magento\Framework\Stdlib\Cookie\FailureToSendException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setVerificationLink($value, $duration = 86400)
    {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());

        $this->_cookieManager->setPublicCookie(
            'verification_link',
            $value,
            $metadata
        );
    }

    /**
     * Set order exist
     *
     * @param                                         string $value
     * @param                                         int    $duration
     * @return                                        void
     * @throws                                        \Magento\Framework\Exception\InputException
     * @throws                                        \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws                                        \Magento\Framework\Stdlib\Cookie\FailureToSendException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setOrderExist($value, $duration = 86400)
    {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());

        $this->_cookieManager->setPublicCookie(
            'order_exist',
            $value,
            $metadata
        );
    }

    /**
     * Remove Country code from Phone number
     *
     * @param  string $country_code Country code.
     * @param  string $phone_number Phone Number.
     * @return string|boolean filtered country code.
     */
    public function filterPhoneNumber($country_code, $phone_number)
    {
        $country_codes = [
        'BD' => '+880',
        'BE' => '+32',
        'BF' => '+226',
        'BG' => '+359',
        'BA' => '+387',
        'BB' => '+1246',
        'WF' => '+681',
        'BL' => '+590',
        'BM' => '+1441',
        'BN' => '+673',
        'BO' => '+591',
        'BH' => '+973',
        'BI' => '+257',
        'BJ' => '+229',
        'BT' => '+975',
        'JM' => '+1876',
        'BV' => '',
        'BW' => '+267',
        'WS' => '+685',
        'BQ' => '+599',
        'BR' => '+55',
        'BS' => '+1242',
        'JE' => '+441534',
        'BY' => '+375',
        'BZ' => '+501',
        'RU' => '+7',
        'RW' => '+250',
        'RS' => '+381',
        'TL' => '+670',
        'RE' => '+262',
        'TM' => '+993',
        'TJ' => '+992',
        'RO' => '+40',
        'TK' => '+690',
        'GW' => '+245',
        'GU' => '+1671',
        'GT' => '+502',
        'GS' => '',
        'GR' => '+30',
        'GQ' => '+240',
        'GP' => '+590',
        'JP' => '+81',
        'GY' => '+592',
        'GG' => '+441481',
        'GF' => '+594',
        'GE' => '+995',
        'GD' => '+1473',
        'GB' => '+44',
        'GA' => '+241',
        'SV' => '+503',
        'GN' => '+224',
        'GM' => '+220',
        'GL' => '+299',
        'GI' => '+350',
        'GH' => '+233',
        'OM' => '+968',
        'TN' => '+216',
        'JO' => '+962',
        'HR' => '+385',
        'HT' => '+509',
        'HU' => '+36',
        'HK' => '+852',
        'HN' => '+504',
        'HM' => '',
        'VE' => '+58',
        'PR' => [
            '+1787',
            '+1939',
        ],
        'PS' => '+970',
        'PW' => '+680',
        'PT' => '+351',
        'SJ' => '+47',
        'PY' => '+595',
        'IQ' => '+964',
        'PA' => '+507',
        'PF' => '+689',
        'PG' => '+675',
        'PE' => '+51',
        'PK' => '+92',
        'PH' => '+63',
        'PN' => '+870',
        'PL' => '+48',
        'PM' => '+508',
        'ZM' => '+260',
        'EH' => '+212',
        'EE' => '+372',
        'EG' => '+20',
        'ZA' => '+27',
        'EC' => '+593',
        'IT' => '+39',
        'VN' => '+84',
        'SB' => '+677',
        'ET' => '+251',
        'SO' => '+252',
        'ZW' => '+263',
        'SA' => '+966',
        'ES' => '+34',
        'ER' => '+291',
        'ME' => '+382',
        'MD' => '+373',
        'MG' => '+261',
        'MF' => '+590',
        'MA' => '+212',
        'MC' => '+377',
        'UZ' => '+998',
        'MM' => '+95',
        'ML' => '+223',
        'MO' => '+853',
        'MN' => '+976',
        'MH' => '+692',
        'MK' => '+389',
        'MU' => '+230',
        'MT' => '+356',
        'MW' => '+265',
        'MV' => '+960',
        'MQ' => '+596',
        'MP' => '+1670',
        'MS' => '+1664',
        'MR' => '+222',
        'IM' => '+441624',
        'UG' => '+256',
        'TZ' => '+255',
        'MY' => '+60',
        'MX' => '+52',
        'IL' => '+972',
        'FR' => '+33',
        'IO' => '+246',
        'SH' => '+290',
        'FI' => '+358',
        'FJ' => '+679',
        'FK' => '+500',
        'FM' => '+691',
        'FO' => '+298',
        'NI' => '+505',
        'NL' => '+31',
        'NO' => '+47',
        'NA' => '+264',
        'VU' => '+678',
        'NC' => '+687',
        'NE' => '+227',
        'NF' => '+672',
        'NG' => '+234',
        'NZ' => '+64',
        'NP' => '+977',
        'NR' => '+674',
        'NU' => '+683',
        'CK' => '+682',
        'XK' => '',
        'CI' => '+225',
        'CH' => '+41',
        'CO' => '+57',
        'CN' => '+86',
        'CM' => '+237',
        'CL' => '+56',
        'CC' => '+61',
        'CA' => '+1',
        'CG' => '+242',
        'CF' => '+236',
        'CD' => '+243',
        'CZ' => '+420',
        'CY' => '+357',
        'CX' => '+61',
        'CR' => '+506',
        'CW' => '+599',
        'CV' => '+238',
        'CU' => '+53',
        'SZ' => '+268',
        'SY' => '+963',
        'SX' => '+599',
        'KG' => '+996',
        'KE' => '+254',
        'SS' => '+211',
        'SR' => '+597',
        'KI' => '+686',
        'KH' => '+855',
        'KN' => '+1869',
        'KM' => '+269',
        'ST' => '+239',
        'SK' => '+421',
        'KR' => '+82',
        'SI' => '+386',
        'KP' => '+850',
        'KW' => '+965',
        'SN' => '+221',
        'SM' => '+378',
        'SL' => '+232',
        'SC' => '+248',
        'KZ' => '+7',
        'KY' => '+1345',
        'SG' => '+65',
        'SE' => '+46',
        'SD' => '+249',
        'DO' => [
            '+1809',
            '+1829',
            '+1849',
        ],
        'DM' => '+1767',
        'DJ' => '+253',
        'DK' => '+45',
        'VG' => '+1284',
        'DE' => '+49',
        'YE' => '+967',
        'DZ' => '+213',
        'US' => '+1',
        'UY' => '+598',
        'YT' => '+262',
        'UM' => '+1',
        'LB' => '+961',
        'LC' => '+1758',
        'LA' => '+856',
        'TV' => '+688',
        'TW' => '+886',
        'TT' => '+1868',
        'TR' => '+90',
        'LK' => '+94',
        'LI' => '+423',
        'LV' => '+371',
        'TO' => '+676',
        'LT' => '+370',
        'LU' => '+352',
        'LR' => '+231',
        'LS' => '+266',
        'TH' => '+66',
        'TF' => '',
        'TG' => '+228',
        'TD' => '+235',
        'TC' => '+1649',
        'LY' => '+218',
        'VA' => '+379',
        'VC' => '+1784',
        'AE' => '+971',
        'AD' => '+376',
        'AG' => '+1268',
        'AF' => '+93',
        'AI' => '+1264',
        'VI' => '+1340',
        'IS' => '+354',
        'IR' => '+98',
        'AM' => '+374',
        'AL' => '+355',
        'AO' => '+244',
        'AQ' => '',
        'AS' => '+1684',
        'AR' => '+54',
        'AU' => '+61',
        'AT' => '+43',
        'AW' => '+297',
        'IN' => '+91',
        'AX' => '+35818',
        'AZ' => '+994',
        'IE' => '+353',
        'ID' => '+62',
        'UA' => '+380',
        'QA' => '+974',
        'MZ' => '+258',
        ];

        $calling_code = '';

        if ($country_code) {
            $calling_code = $country_codes[$country_code];
            $calling_code = is_array($calling_code) ? $calling_code[0] : $calling_code;
        }
        //remove country code
        $res = str_replace($calling_code, "", $phone_number);

        // filter non digit characters
        $res = substr(preg_replace('/[^0-9]+/', '', $res), -10);

        // check if phone number length is 10 digit
        if (strlen($res) === 10) {
            return "$res";
        }

        return false;
    }

    /**
     * Get Name Object From Customer
     *
     * @param  mixed $order
     * @param  mixed $addressObj
     * @return array
     */
    public function getCustomerNameObject($order, $addressObj)
    {
        $firstName = $order->getCustomerFirstname();
        $firstLastName  = $order->getCustomerLastname();

        if ($addressObj) {
            $firstName = $addressObj->getData('firstname');
            $firstLastName = $addressObj->getData('lastname');
        }

        return ["first_name"=>$firstName,"first_last_name"=>$firstLastName];
    }

    /**
     * Tonder order update API
     *
     * @param  mixed  $order
     * @param  string $antiFraudMeta
     * @param  string $tonderOrderId
     * @return void
     */
    public function tonderOrderUpdate($order, $antiFraudMeta, $tonderOrderId)
    {
        $quoteItems = $order->getAllVisibleItems();
        $currencyCode = $order->getStoreCurrencyCode();
        $publicKey = $this->_helper->getPublicKey();
        $apiBaseUrl = $this->_helper->getApiBaseUrl();
        $debugMode = $this->_helper->debugMode();
        $widgetVersion = $this->_helper->getWidgetVersion();
        $name = [];
        $contact = [];
        $address = [];
        $telephone = "";
        if ($order->getBillingAddress()) {
            $street1 = implode(" ", $order->getBillingAddress()->getStreet(1));
            $street2 = implode(" ", $order->getBillingAddress()->getStreet(2));

            $address = [
                "address_line_1"=>$street1,
                "address_line_2"=>$street2,
                "locality"=>$order->getBillingAddress()->getCity(),
                "region_name_or_code"=>$this->getTonderRegionCode($order->getBillingAddress()->getRegionId()),
                "postal_code"=>$order->getBillingAddress()->getPostcode(),
                "country_code"=>$this->getCountryCode($order->getBillingAddress()->getCountryId())
            ];

            $name = $this->getCustomerNameObject($order, $order->getBillingAddress());
            $contact = ["email"=>$order->getBillingAddress()->getEmail()];

            $telephone = $this->filterPhoneNumber(
                $order->getBillingAddress()->getCountryId(),
                $order->getBillingAddress()->getTelephone()
            );
            if ($telephone) {
                $contact ["phone_1"]=$telephone;
            }
        }

        $billingDetails = ["name"=>$name,"contact"=>$contact,"address"=>$address];
        $shippingDetails = ["name"=>$name,"contact"=>$contact,"address"=>$address];

        $consumerDetails = [
            "contact"=>[
                "email"=>$order->getBillingAddress()->getEmail()
            ],
            "name" => $this->getCustomerNameObject($order, false),
        ];
        if ($telephone) {
            $consumerDetails["contact"]["phone_1"] = $telephone;
        }

        if ($order->getShippingAddress()) {
            $street1 = implode(" ", $order->getShippingAddress()->getStreet(1));
            $street2 = implode(" ", $order->getShippingAddress()->getStreet(2));

            $address = [
                "address_line_1"=>$street1,
                "address_line_2"=>$street2,
                "locality"=>$order->getShippingAddress()->getCity(),
                "region_name_or_code"=>$this->getTonderRegionCode($order->getShippingAddress()->getRegionId()),
                "postal_code"=>$order->getShippingAddress()->getPostcode(),
                "country_code"=>$this->getCountryCode($order->getShippingAddress()->getCountryId())
            ];

            $name = $this->getCustomerNameObject($order, $order->getShippingAddress());
            $contact = ["email"=>$order->getShippingAddress()->getEmail()];

            $telephone = $this->filterPhoneNumber(
                $order->getShippingAddress()->getCountryId(),
                $order->getShippingAddress()->getTelephone()
            );
            if ($telephone) {
                $contact ["phone_1"]=$telephone;
            }

            $shippingDetails = ["name"=>$name,"contact"=>$contact,"address"=>$address];
        }

        $items = [];
        if ($quoteItems) {
            foreach ($quoteItems as $quoteItem) {
                $items[] = [
                    "item_total_amount"=>[
                        "amount"=>(int)($quoteItem->getRowTotal()*100),
                        "currency_code"=>$currencyCode
                    ],
                    "description"=> $quoteItem->getName(),
                    "id"=> $quoteItem->getSku(),
                    "quantity"=> $quoteItem->getQtyOrdered(),
                    "unit_amount"=>[
                        "amount"=> (int)($quoteItem->getPrice()*100),
                        "currency_code"=> $currencyCode
                        ]
                    ];
            }
        }

        $URL = $apiBaseUrl.'/order/'.$tonderOrderId;
        $storeTitle = $this->_storeManager->getStore()->getGroup()->getName();
        $orderIncrementId = $order->getIncrementId();
        $description = "Magento - ".$storeTitle." Order #".$orderIncrementId;

        $postArray = [
            "order_total_amount" => [
                "amount"=>(int)($order->getGrandTotal()*100),
                "currency_code"=>$currencyCode
            ],
            "description"=> $description,
            "external_system_order_id" => $orderIncrementId,
            "purchases"=>$items,
            "shipping_details"=>$shippingDetails,
            "billing_details"=>$billingDetails,
            "consumer_details"=>$consumerDetails
        ];

        $jsonData = json_encode($postArray);

        if ($debugMode) {
            $this->_logger->info("-----PaymentMethod Update Order starts-----");
            $this->_logger->info("Posted Data:");
            $this->_logger->info($jsonData);
        }

        $this->_curlCustom->addHeader("X-Api-Client-Key", $publicKey);
        $this->_curlCustom->addHeader("X-Cash-Anti-Fraud-Metadata", $antiFraudMeta);
        $this->_curlCustom->addHeader("X-Cash-Checkout-Widget-Version", $widgetVersion);
        $this->_curlCustom->addHeader("Content-Type", "application/json");
        $this->_curlCustom->patch($URL, $jsonData);

        $response = $this->_curlCustom->getBody();
        if ($debugMode) {
            $this->_logger->info("Received Data:");
            $this->_logger->info($response);
            $this->_logger->info("-----PaymentMethod Update Order ends-----");
        }
    }
    /**
     * Update charge status
     *
     * @param  string $tonderOrderId
     * @return void
     */
    public function updateCharge($tonderOrderId)
    {
        $tonder = $this->_tonderFactory->create()->load($tonderOrderId, 'tonder_order_id');
        $tonder->setTonderCharge(2);
        $tonder->save();
    }

    /**
     * Convert to string format
     *
     * @param  mixed $data
     * @return string
     */
    public function dataStringify($data)
    {
        // phpcs:ignore
        switch (gettype($data)) {
            case 'boolean':
                return $data ? 'true' : 'false';
            case 'NULL':
                return 'null';
            case 'object':
            case 'array':
                $expressions = [];
                foreach ($data as $c_key => $c_value) {
                    $expressions[ $this->dataStringify($c_key) ] = $this->dataStringify($c_value);
                }
                return $expressions;
            default:
                return "{$data}";
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
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        if ($country->getData('iso3_code')) {
            $countryCode = $country->getData('iso3_code');
        }
        return  (empty($countryCode) || $countryCode == 'None') ? 'MEX' : $countryCode;
    }

    /**
     * Get Region code mapped with Tonder
     *
     * @param  string $regionCode
     * @return string
     */
    public function getTonderRegionCode($regionCode)
    {
        $region = $this->_regionFactory->create()->load($regionCode);
        $return = $region->getCode() ?? $regionCode;
        return (empty($return) || $return == 'None') ? 'MEX' : $return;
    }

    /**
     * Identify StorePickup
     *
     * @param mixed $order
     *
     * @return boolean
     */
    public function identifyStorePickup($order)
    {
        $name = $this->getCustomerNameObject($order, $order->getShippingAddress());

        // search keyword
        $searchword = 'Pickup';

        // search in following section
        $searchableArray = array_values($name);
        $searchableArray[] = $order->getShippingMethod();
        $searchableArray[] = $order->getShippingDescription();

        // if both shipping first name and last name is '-' then count as 'pickup'
        $searchableArray[] = count(
            array_filter(
                array_values($name),
                function ($var) {
                    return $var === '-';
                }
            )
        ) === count($name) ? 'pickup' : '-';

        $arr = array_filter(
            $searchableArray,
            function ($var) use ($searchword) {
                return stripos($var, $searchword) !== false;
            }
        );
        return count($arr) > 0;
    }

    /**
     * Parse error and this method is used by Signifyd to intercept and create cases for orders
     *
     * @param  array  $response create charge response
     *
     * @return string error message
     */
    public function error($response)
    {
        $message = $response['detail']["message"] ?? 'Could not create charge';

        return $message;
    }

    /**
     * Parse language according to tonder api requirement
     *
     * @param  string  lang
     *
     * @return string  res
     */
    public function parseLanguage($lang)
    {
        $res = 'es';
        if ($lang == 'en' || stripos($lang, 'en') !== false) {
            $res = 'en';
        } elseif (strlen($lang) === 2) {
            $res = $lang;
        }

        return $res;
    }
}
