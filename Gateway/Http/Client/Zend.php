<?php

namespace Tonder\Payment\Gateway\Http\Client;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Tonder\Payment\Gateway\Request\AbstractDataBuilder;
use Tonder\Payment\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class Zend
 */
class Zend extends \Magento\Payment\Gateway\Http\Client\Zend
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var ConverterInterface | null
     */
    private $converter;

    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Json
     */
    protected $_jsonFramework;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ZendClientFactory $clientFactory
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param Logger $logger2
     * @param Json $_jsonFramework
     * @param SerializerInterface $serializer
     * @param ConverterInterface|null $converter
     */
    public function __construct(
        ZendClientFactory                 $clientFactory,
        \Magento\Payment\Model\Method\Logger $logger,
        Logger                               $logger2,
        Json                                 $_jsonFramework,
        SerializerInterface                  $serializer,
        ConverterInterface                   $converter = null

    ) {
        $this->clientFactory = $clientFactory;
        $this->converter = $converter;
        $this->logger = $logger2;
        $this->serializer = $serializer;
        $this->_jsonFramework = $_jsonFramework;
        parent::__construct($clientFactory, $logger, $converter);
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $logInfo = [
            'request_body' => $this->maskData($transferObject->getBody()),
            'request_url' => $transferObject->getUri()
        ];
        /** @var ZendClient $client */
        $client = $this->clientFactory->create();
        $client->setMethod($transferObject->getMethod());
        $client->setRawData($transferObject->getBody());

        $client->setHeaders($transferObject->getHeaders());
        $client->setUri($transferObject->getUri());
        $client->setEncType('application/json');
        $result = [];
        try {
            $response = '{"status":"200","message":"Success","response":{"message":"Success","status":200,"data":{"id":"pi_3MvBWgIRGzBlvVk20FyJKFOY","object":"payment_intent","amount":91.97,"amount_capturable":0,"amount_details":{"tip":{}},"amount_received":9197,"application":null,"application_fee_amount":null,"automatic_payment_methods":null,"canceled_at":null,"cancellation_reason":null,"capture_method":"automatic","charges":{"object":"list","data":[{"id":"ch_3MvBWgIRGzBlvVk200e3XTh7","object":"charge","amount":9197,"amount_captured":9197,"amount_refunded":0,"application":null,"application_fee":null,"application_fee_amount":null,"balance_transaction":"txn_3MvBWgIRGzBlvVk20qpvc90s","billing_details":{"address":{"city":null,"country":null,"line1":null,"line2":null,"postal_code":null,"state":null},"email":null,"name":null,"phone":null},"calculated_statement_descriptor":"AYQUECOMPARTIR","captured":true,"created":1681097778,"currency":"mxn","customer":"cus_NgYdDxT1GzyksO","description":"transaction","destination":null,"dispute":null,"disputed":false,"failure_balance_transaction":null,"failure_code":null,"failure_message":null,"fraud_details":{},"invoice":null,"livemode":false,"metadata":{},"on_behalf_of":null,"order":null,"outcome":{"network_status":"approved_by_network","reason":null,"risk_level":"normal","risk_score":53,"seller_message":"Payment complete.","type":"authorized"},"paid":true,"payment_intent":"pi_3MvBWgIRGzBlvVk20FyJKFOY","payment_method":"pm_1MvBWfIRGzBlvVk2xYMywK3d","payment_method_details":{"card":{"brand":"visa","checks":{"address_line1_check":null,"address_postal_code_check":null,"cvc_check":null},"country":"US","exp_month":5,"exp_year":2025,"fingerprint":"RLvPUCoR862px8JK","funding":"credit","installments":null,"last4":"4242","mandate":null,"network":"visa","network_token":{"used":false},"three_d_secure":null,"wallet":null},"type":"card"},"receipt_email":null,"receipt_number":null,"receipt_url":"https://pay.stripe.com/receipts/payment/CAcaFwoVYWNjdF8xTVpmM3JJUkd6Qmx2VmsyKLKIzqEGMgYiO15xmoY6LBbxc-90SrgfvqulRWtEQE9cQk_oVdnVR103sHsB78UEucEurkWR48CUOe1q","refunded":false,"refunds":{"object":"list","data":[],"has_more":false,"total_count":0,"url":"/v1/charges/ch_3MvBWgIRGzBlvVk200e3XTh7/refunds"},"review":null,"shipping":null,"source":null,"source_transfer":null,"statement_descriptor":null,"statement_descriptor_suffix":null,"status":"succeeded","transfer_data":null,"transfer_group":null}],"has_more":false,"total_count":1,"url":"/v1/charges?payment_intent=pi_3MvBWgIRGzBlvVk20FyJKFOY"},"client_secret":"pi_3MvBWgIRGzBlvVk20FyJKFOY_secret_17LKzy0rXhTl7DBEg6yIk98Zv","confirmation_method":"automatic","created":1681097778,"currency":"mxn","customer":"cus_NgYdDxT1GzyksO","description":"transaction","invoice":null,"last_payment_error":null,"latest_charge":"ch_3MvBWgIRGzBlvVk200e3XTh7","livemode":false,"metadata":{},"next_action":null,"on_behalf_of":null,"payment_method":"pm_1MvBWfIRGzBlvVk2xYMywK3d","payment_method_options":{"card":{"installments":null,"mandate_options":null,"network":null,"request_three_d_secure":"automatic"}},"payment_method_types":["card"],"processing":null,"receipt_email":null,"review":null,"setup_future_usage":null,"shipping":null,"source":null,"statement_descriptor":null,"statement_descriptor_suffix":null,"status":"succeeded","transfer_data":null,"transfer_group":null},"transaction_status":"succeeded"}}';
                //remove later
//            $response = $client->request();
            try {
//                $result = $this->serializer->unserialize($response->getBody());
                $result = $this->serializer->unserialize($response);
            } catch (\Exception $e) {
                $result['Message'] = "Invalid body returned!";
            }
//            $result['ResponseCode'] = $response->getStatus();
            $result['ResponseCode'] = $result['status'];
//            $result['ResponseMessage'] = $response->getMessage();
            $result['ResponseMessage'] = $result['message'];

            $result['message'] .= " Details: " . $result['ResponseCode'] . " - " . $result['ResponseMessage'];
//            $logInfo['response_body'] = $response->getBody();
            $logInfo['response_body'] = $response;

        } catch (\RuntimeException $e) {
            throw new ClientException(
                __($e->getMessage())
            );
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->logger->info('Info log: ', $logInfo);
        }
        return $result;
    }

    /**
     * @param $requestBodyArray
     * @return array|mixed
     */
    public function maskData($requestBodyArray)
    {
        if (isset($requestBodyArray[AbstractDataBuilder::PURCHASE])) {
            $requestBodyArray = $this->updateRequestLogInfo($requestBodyArray, AbstractDataBuilder::PURCHASE);
        } elseif (isset($requestBodyArray[AbstractDataBuilder::AUTHORIZE])) {
            $requestBodyArray = $this->updateRequestLogInfo($requestBodyArray, AbstractDataBuilder::AUTHORIZE);
        } elseif (isset($requestBodyArray[AbstractDataBuilder::CARD_VERIFICATION])) {
            $requestBodyArray = $this->updateRequestLogInfo($requestBodyArray, AbstractDataBuilder::CARD_VERIFICATION);
        } elseif (isset($requestBodyArray[AbstractDataBuilder::KOUNT_INQUIRY])) {
            $pan = $requestBodyArray[AbstractDataBuilder::KOUNT_INQUIRY]['payment_token'];
            if ($pan != '') {
                $requestBodyArray[AbstractDataBuilder::KOUNT_INQUIRY]['payment_token'] = substr_replace($pan, '***', 3, -3);
            }
        } elseif ($type = $this->checkRequest3DSecure($requestBodyArray)) {
            $requestBodyArray = $this->updateRequestLogInfo($requestBodyArray, $type);
        }
        return $requestBodyArray;
    }

    /**
     * @param $requestBodyArray
     * @param $type
     * @return array
     */
    protected function updateRequestLogInfo($requestBodyArray, $type)
    {
        $pan = $requestBodyArray[$type]['pan'];
        $requestBodyArray[$type]['pan'] = substr_replace($pan, '***', 3, -3);
        $requestBodyArray[$type]['expdate'] = '****';
        if (isset($requestBodyArray[$type]['cvd_info'])) {
            $requestBodyArray[$type]['cvd_info']['cvd_value'] = '***';
        }
        return $requestBodyArray;
    }

    /**
     * @param $requestBodyArray
     * @return mixed|string
     */
    public function checkRequest3DSecure($requestBodyArray)
    {
        $typesRequest = [
            AbstractDataBuilder::CARD_LOOKUP,
            AbstractDataBuilder::CAVV_PREAUTH,
            AbstractDataBuilder::CAVV_PURCHASE
        ];

        foreach ($typesRequest as $type) {
            if (isset($requestBodyArray[$type]) && isset($requestBodyArray[$type]['pan'])) {
                return $type;
            }
        }
        return '';
    }
}
