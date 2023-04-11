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
            $response = $client->request();
            try {
                $result = $this->serializer->unserialize($response->getBody());
            } catch (\Exception $e) {
                $result['Message'] = "Invalid body returned!";
            }
            $result['ResponseCode'] = $response->getStatus();
            $result['ResponseMessage'] = $response->getMessage();

            $result['message'] .= " Details: " . $result['ResponseCode'] . " - " . $result['ResponseMessage'];
            $logInfo['response_body'] = $response->getBody();

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
