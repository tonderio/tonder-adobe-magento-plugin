<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Http\Client;

use Laminas\Http\Client;
use Laminas\Http\ClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Tonder\Payment\Logger\Logger;
use Tonder\Payment\Gateway\Request\AbstractDataBuilder;

/**
 * Class Zend
 */
class Zend  implements ClientInterface
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ClientFactory $clientFactory
     * @param Logger $logger
     * @param Json $jsonFramework
     * @param ConverterInterface|null $converter
     */
    public function __construct(
        ClientFactory                 $clientFactory,
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
         /** @var Client $client */
        $client = $this->clientFactory->create();

        $client->setMethod($transferObject->getMethod());
        $client->setRawBody($transferObject->getBody());
        $client->setOptions(array('timeout'=> 30));
        $client->setHeaders($transferObject->getHeaders());
        $client->setUri($transferObject->getUri());
        $client->setEncType('application/json');

        try {
            $response = $client->send();
            try {
                $result = $this->serializer->unserialize($response->getBody());
                if (!isset($result['message'])) {
                    $result['message'] = "";
                }
            } catch (\Exception $e) {
                $result['message'] = "Invalid body returned!";
            }
            $result['ResponseCode'] = $response->getStatusCode();
            $result['ResponseMessage'] = $response->getReasonPhrase();

            $result['message'] .= " Details: " . $result['ResponseCode'] . " - " . $result['ResponseMessage'];
            $logInfo['response_body'] = [$response->getBody()];

        } catch (\RuntimeException $e) {
            throw new ClientException( __($e->getMessage()) );
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
