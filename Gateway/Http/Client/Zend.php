<?php

namespace Tonder\Payment\Gateway\Http\Client;

use Tonder\Payment\Gateway\Request\AbstractDataBuilder;
use Tonder\Payment\Logger\Logger;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class Zend
 */
class Zend extends \Magento\Payment\Gateway\Http\Client\Zend
{
    /**
     * @var ZendClientFactory
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
     * @param ZendClientFactory $clientFactory
     * @param Logger $logger
     * @param ConverterInterface | null $converter
     * @param Json $_jsonFramework
     */
    public function __construct(
        ZendClientFactory $clientFactory,
        Logger $logger,
        Json $_jsonFramework,
        ConverterInterface $converter = null

    ) {
        $this->clientFactory = $clientFactory;
        $this->converter = $converter;
        $this->logger = $logger;
        $this->_jsonFramework = $_jsonFramework;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $requestBodyXml = simplexml_load_string($transferObject->getBody());
        $requestBodyArray = $this->_jsonFramework->unserialize($this->_jsonFramework->serialize($requestBodyXml));
        $logInfo = [
            'request_body' => $this->maskData($requestBodyArray),
            'request_url' => $transferObject->getUri()
        ];
        $result = [];
        /** @var ZendClient $client */
        $client = $this->clientFactory->create();
        $client->setConfig($transferObject->getClientConfig());
        $client->setMethod($transferObject->getMethod());
        $client->setRawData($transferObject->getBody());

        $client->setHeaders($transferObject->getHeaders());
        $client->setUrlEncodeBody($transferObject->shouldEncode());
        $client->setUri($transferObject->getUri());

        try {
            $response = $client->request();
            $result = $this->converter
                ? $this->converter->convert($response->getBody())
                : [$response->getBody()];
            $logInfo['response_body'] = $result;
            if ($result['ResponseCode'] > "050" && $result['ResponseCode'] < "099") {
                $result['Message'] = "Your credit card information is incorrect. Please try again!";
            }
        } catch (\Zend_Http_Client_Exception $e) {
            throw new \Magento\Payment\Gateway\Http\ClientException(
                __($e->getMessage())
            );
        } catch (\Magento\Payment\Gateway\Http\ConverterException $e) {
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
            AbstractDataBuilder::THREEDS_AUTHENTICATION,
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
