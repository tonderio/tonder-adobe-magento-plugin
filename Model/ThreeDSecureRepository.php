<?php
namespace Tonder\Payment\Model;

use Tonder\Payment\Gateway\Request\AbstractDataBuilder;
use Tonder\Payment\Logger\Logger;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as PaymentTokenCollectionFac;

class ThreeDSecureRepository implements \Tonder\Payment\Api\ThreeDSecureInterface
{
    const STORE_ID = 'store_id';
    const API_TOKEN = 'api_token';
    const CARDHOLDER_NAME = 'cardholder_name';
    const PAN = 'pan';
    const DATA_KEY = 'data_key';
    const EXPDATE = 'expdate';
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const THREEDS_COMPLETION_IND = 'threeds_completion_ind';
    const REQUEST_TYPE = 'request_type';
    const NOTIFICATION_URL = 'notification_url';
    const PURCHASE_DATE = 'purchase_date';
    const CHALLENGE_WINDOW_SIZE = 'challenge_windowsize';
    const BROWSER_USERAGENT= 'browser_useragent';
    const BROWSER_JAVA_ENABLED = 'browser_java_enabled';
    const BROWSER_SCREEN_HEIGHT = 'browser_screen_height';
    const BROWSER_SCREEN_WIDTH = 'browser_screen_width';
    const BROWSER_LANGUAGE = 'browser_language';
    const REQUEST_CHALLENGE = 'request_challenge';
    const THREEDS_VERSION = 'threeds_version';
    const EMAIL = 'email';
    const BILL_ADDRESS = 'bill_address1';
    const BILL_POSTAL_CODE = 'bill_postal_code';
    const BILL_CITY = 'bill_city';
    const BILL_COUNTRY = 'bill_country';
    const BILL_PROVINCE = 'bill_province';
    const SHIP_ADDRESS = 'ship_address1';
    const SHIP_POSTAL_CODE = 'ship_postal_code';
    const SHIP_CITY = 'ship_city';
    const SHIP_COUNTRY = 'ship_country';
    const SHIP_PROVINCE = 'ship_province';
    const ORDER_ID      = 'order_id';
    const REQUEST_MPI_TYPE = 'type';
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var \Tonder\Payment\Gateway\Http\TransferFactory
     */
    protected $transferFactory;

    /**
     * @var \Tonder\Payment\Gateway\Http\Client\Zend
     */
    protected $clientZend;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_jsonFramework;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $_store;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var PaymentTokenCollectionFac
     */
    private $paymentTokenCollectionFactory;

    /**
     * store id of merchant
     */
    private $store_id;

    /**
     * api token of merchant
     */
    private $api_token;

    /**
     * ThreeDSecureRepository constructor.
     *
     * @param Logger $logger
     * @param \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool
     * @param \Tonder\Payment\Gateway\Http\TransferFactory $transferFactory
     * @param \Tonder\Payment\Gateway\Http\Client\Zend $clientZend
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonFramework
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\UrlInterface $url
     * @param ConfigInterface $config
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param ResultFactory $resultFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Locale\Resolver $store
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param PaymentTokenCollectionFac $paymentTokenCollectionFactory
     */
    public function __construct(
        Logger $logger,
        \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool,
        \Tonder\Payment\Gateway\Http\TransferFactory $transferFactory,
        \Tonder\Payment\Gateway\Http\Client\Zend $clientZend,
        \Magento\Framework\Serialize\Serializer\Json $jsonFramework,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\UrlInterface $url,
        ConfigInterface $config,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\Resolver $store,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        PaymentTokenCollectionFac $paymentTokenCollectionFactory
    ) {
        $this->logger = $logger;
        $this->commandPool = $commandPool;
        $this->transferFactory = $transferFactory;
        $this->clientZend = $clientZend;
        $this->_jsonFramework = $jsonFramework;
        $this->formKey = $formKey;
        $this->_url = $url;
        $this->config = $config;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->resultFactory = $resultFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_store = $store;
        $this->timezone = $timezone;
        $this->paymentTokenCollectionFactory = $paymentTokenCollectionFactory;
    }

    /**
     * @param mixed $payload
     *
     * @return bool|false|mixed|string
     */
    public function get3DInfo($payload)
    {
        $result = [];
        try {
            if (is_array($payload)) {
                $this->store_id  = $this->config->getValue(self::STORE_ID);
                $this->api_token = $this->config->getValue(self::API_TOKEN);
                $order = $this->getOrder();
                $payment = $order->getPayment();
                $paymentDataObject = $this->paymentDataObjectFactory->create($payment);
                $payment = $paymentDataObject->getPayment();
                $merchantUrl = $this->_url->getUrl('moneris/order/postback') . '?order_id=' . $order->getId();
                $cardData = $payload['cardData'] ?? null;
                $token = '';
                if (isset($payload['use_vault']) && $payload['use_vault']) {
                    $token = $this->paymentTokenCollectionFactory->create()
                        ->addFieldToFilter('customer_id', $order->getCustomerId())
                        ->addFieldToFilter('public_hash', $payment->getAdditionalInformation('public_hash'))
                        ->getFirstItem();
                    if (!$token->getGatewayToken()) {
                        throw new LocalizedException(__('Could not find token for this card. Please use a new one.'));
                    }
                    $cardData = $this->_jsonFramework->unserialize($token->getDetails());
                }
                if ($cardData == null) {
                    throw new \Exception(__('Missing Card Information.'));
                }
                $pan = $cardData['accountNumber'] ?? '';
                // mpi 3DS authentication request
                $threeDAuthentication = $this->getThreeDAuthenticationData($order, $payload, $cardData, $pan, $token, $merchantUrl);
                $mpiResponse = $this->sendMPIRequest($this->store_id, $this->api_token, $threeDAuthentication);
                $result =  $mpiResponse;
                $result['authentication'] = true;
                $resultHandle = $this->handleAuthenticationThreeD($mpiResponse, $paymentDataObject, $order);
                if (!$resultHandle) {
                    // Failed Authentication
                    $order->addCommentToStatusHistory('3DS Authentication Request: ' . $mpiResponse['Message'])->setIsCustomerNotified(false)->save();
                    $result['authentication'] = false;
                    $result['redirect_url'] = $this->_url->getUrl('moneris/order/cancel') . '?message=' . __('Failed Authentication !');
                }
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
            $result['authentication'] = false;
            $result['redirect_url'] = $this->_url->getUrl('moneris/order/cancel') . '?message=' . $exception->getMessage();
        }

        return $this->_jsonFramework->serialize($result);
    }

    /**
     * @param $response
     * @param $paymentDataObject
     * @param $order
     * @return bool
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function handleAuthenticationThreeD($response, $paymentDataObject, $order)
    {
        if (!isset($response['TransStatus'])) {
            return false;
        }
        $arguments['payment'] = $paymentDataObject;
        $arguments['response'] = $response;
        $arguments['amount'] = sprintf('%.2F', $order->getTotalDue());
        switch ($response['TransStatus']) {
            case "Y":
            case "A":
                $this->commandPool->get('three_d_secure')->execute($arguments);
                return true;
            case "N":
            case "U":
                $paymentAction = $order->getPayment()->getMethodInstance()->getConfigPaymentAction();
                $order->getPayment()->setAdditionalInformation('3ds_non_authenticated', true);
                if ($paymentAction == 'authorize') {
                    $this->commandPool->get('authorize')->execute($arguments);
                } else {
                    $this->commandPool->get('capture')->execute($arguments);
                }
                return true;
            case "C":
                return true;
            default:
                return false;
        }
    }

    /**
     * @param $store_id
     * @param $api_token
     * @param $pan
     * @param $token
     * @param $merchantUrl
     * @return array|mixed|string|null
     * @throws \Exception
     */
    public function cardLookupRequest($store_id, $api_token, $pan, $token, $merchantUrl)
    {
        $txnArray = [
            self::REQUEST_MPI_TYPE => AbstractDataBuilder::CARD_LOOKUP,
            self::PAN => $pan,
            self::NOTIFICATION_URL=> $merchantUrl
        ];
        if (empty($txnArray[self::PAN]) && $token->getGatewayToken()) {
            unset($txnArray[self::PAN]);
            $txnArray[self::DATA_KEY] = $token->getGatewayToken();
        }
        return $this->sendMPIRequest($store_id, $api_token, $txnArray);
    }

    /**
     * @param $store_id
     * @param $api_token
     * @param $txnArray
     * @return array|mixed|string|null
     * @throws \Exception
     */
    public function sendMPIRequest($store_id, $api_token, $txnArray)
    {
        try {
            if (is_array($txnArray)) {
                $transferO = $this->transferFactory->create([
                    self::STORE_ID => $store_id,
                    self::API_TOKEN => $api_token,
                    AbstractDataBuilder::REPLACE_KEY => $txnArray
                ]);
                return $this->clientZend->placeRequest($transferO);
            } else {
                throw new \Exception(__('Please valid txtArray'));
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * @param $order
     * @param $payload
     * @param $cardData
     * @param $pan
     * @param $token
     * @param $merchantUrl
     * @return array
     * @throws \Exception
     */
    public function getThreeDAuthenticationData($order, $payload, $cardData, $pan, $token, $merchantUrl)
    {
        $billingAddress = $order->getBillingAddress();
        $userAgent = $payload['userAgent'] ?? 'none';
        $txnArray = [
            self::REQUEST_MPI_TYPE => AbstractDataBuilder::THREEDS_AUTHENTICATION,
            self::ORDER_ID => $order->getRealOrderId(),
            self::CARDHOLDER_NAME => $cardData['cardHolderName'] ?? $billingAddress->getFirstName() . ' ' . $billingAddress->getLastName() ,
            self::PAN => $pan,
            self::EXPDATE => $this->getExpiryDate($cardData),
            self::AMOUNT  => sprintf('%.2F', $order->getTotalDue()),
            self::THREEDS_COMPLETION_IND => 'Y',
            self::REQUEST_TYPE => '01', //(01=payment|02=recur)
            self::NOTIFICATION_URL => $merchantUrl,
            self::BROWSER_USERAGENT => $userAgent,
            self::PURCHASE_DATE => $this->timezone->date(new \DateTime($order->getCreatedAt()))->format('YmdHis'),
            self::CHALLENGE_WINDOW_SIZE => "03",
            self::BROWSER_JAVA_ENABLED => 'true',
            self::BROWSER_SCREEN_HEIGHT => '1000',
            self::BROWSER_SCREEN_WIDTH => '1920',
            self::BROWSER_LANGUAGE => $this->_store->getLocale(),
            self::THREEDS_VERSION => 2,
            self::REQUEST_CHALLENGE => 'Y'
        ];
        $txnArray = $this->mergeAddressFields($txnArray, $order);
        $txnArray = $this->validateAddressFields($txnArray);
        if (empty($txnArray[self::PAN]) && $token->getGatewayToken()) {
            unset($txnArray[self::PAN]);
            $txnArray[self::DATA_KEY] = $token->getGatewayToken();
        }
        return $txnArray;
    }

    /**
     * @param $cardData
     * @return string
     */
    public function getExpiryDate($cardData)
    {
        if (isset($cardData['expirationDate'])) {
            $expCard = explode('/', $cardData['expirationDate']);
            $expMonth = $expCard[0];
            $expYear  = $expCard[1];
        } else {
            $expMonth = $cardData['expMonth'] ?? '';
            $expYear = $cardData['expYear'] ?? '';
        }
        if ($expMonth && strlen($expMonth) == 1) {
            $expMonth = "0" . $expMonth;
        }
        if ($expYear && strlen($expYear) == 4) {
            $expYear = substr($expYear, 2);
        }
        return $expYear . $expMonth;
    }

    /**
     * @param $address
     * @return string
     */
    public function getRegionCode($address)
    {
        if (empty($address->getRegionCode()) || strlen($address->getRegionCode()) > 2) {
            return 'ON';
        }
        return $address->getRegionCode();
    }

    /**
     * validate length limit of field in 3ds authentication data
     * @param $txnArr
     * @return mixed
     */
    public function validateAddressFields($txnArr)
    {
        $fields = [
           self::BILL_ADDRESS => 50,
           self::BILL_CITY => 50,
           self::BILL_POSTAL_CODE => 16,
           self::SHIP_ADDRESS => 50,
           self::SHIP_CITY => 50,
           self::SHIP_POSTAL_CODE => 16,
       ];
        foreach ($fields as $field => $value) {
            if (isset($txnArr[$field]) && $txnArr[$field] > $value) {
                unset($txnArr[$field]);
            }
        }
        return $txnArr;
    }

    /**
     * optional info to request
     * @param $txnArray
     * @param $order
     * @return array
     */
    public function mergeAddressFields($txnArray, $order)
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $billFields = [];
        $shipFields = [];
        if (class_exists('\League\ISO3166\ISO3166')) {
            $iso3166 = ObjectManager::getInstance()->create('League\ISO3166\ISO3166');
        }
        if ($billingAddress) {
            if (isset($iso3166)) {
                $billCountry = $iso3166->alpha2($billingAddress->getCountryId());
            }
            $billFields = [
                self::EMAIL => $billingAddress->getEmail(),
                self::BILL_ADDRESS => $billingAddress->getStreetLine(1),
                self::BILL_CITY => $billingAddress->getCity(),
                self::BILL_PROVINCE => $this->getRegionCode($billingAddress),
                self::BILL_COUNTRY => $billCountry['numeric'] ?? '124',
                self::BILL_POSTAL_CODE => $billingAddress->getPostcode()
            ];
        }
        if ($shippingAddress) {
            if (isset($iso3166)) {
                $shipCountry = $iso3166->alpha2($shippingAddress->getCountryId());
            }
            $shipFields = [
                self::SHIP_ADDRESS => $shippingAddress->getStreetLine(1),
                self::SHIP_CITY => $shippingAddress->getCity(),
                self::SHIP_PROVINCE => $this->getRegionCode($shippingAddress),
                self::SHIP_COUNTRY => $shipCountry['numeric'] ?? '124',
                self::SHIP_POSTAL_CODE => $shippingAddress->getPostcode()
            ];
        }
        return array_merge($txnArray, $billFields, $shipFields);
    }
}
