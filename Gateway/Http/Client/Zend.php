<?php

namespace Tonder\Payment\Gateway\Http\Client;


use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Tonder\Payment\Gateway\Request\AbstractDataBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Tonder\Payment\Logger\Logger;

/**
 * Class Zend
 */
class Zend  implements ClientInterface
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ZendClientFactory $clientFactory
     * @param Logger $logger
     * @param Json $jsonFramework
     * @param ConverterInterface|null $converter
     */
    public function __construct(
        ZendClientFactory                 $clientFactory,
        Logger                               $logger,
        SerializerInterface                  $serializer,
        ConverterInterface                   $converter = null

    ) {
        $this->clientFactory = $clientFactory;
        $this->converter = $converter;
        $this->logger = $logger;
        $this->serializer = $serializer;
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
        $result = [];
         /** @var ZendClient $client */
        $client = $this->clientFactory->create();

        $client->setMethod($transferObject->getMethod());
        $client->setRawData($transferObject->getBody());

        $client->setHeaders($transferObject->getHeaders());
        $client->setUri($transferObject->getUri());
        $client->setEncType('application/json');

        try {
            $response = $client->request();
            try {
                $result = $this->serializer->unserialize($response->getBody());
                if (!isset($result['message'])) {
                    $result['message'] = "";
                }
            } catch (\Exception $e) {
                $result['message'] = "Invalid body returned!";
            }
            $result['ResponseCode'] = $response->getStatus();
            $result['ResponseMessage'] = $response->getMessage();

            $result['message'] .= " Details: " . $result['ResponseCode'] . " - " . $result['ResponseMessage'];
            $logInfo['response_body'] = [$response->getBody()];

        } catch (\RuntimeException $e) {
            throw new ClientException( __($e->getMessage()) );
        } catch (\Magento\Payment\Gateway\Http\ConverterException  $e) {
            throw $e;
        } finally {
            $this->logger->info('Info log: ', [$logInfo]);
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
        return $requestBodyArray;
    }
}
