<?php

namespace Tonder\Payment\Helper;

use Firebase\JWT\JWT;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;
use Laminas\Http\Client;
use Laminas\Http\ClientFactory;

class SkyFlowProcessor extends AbstractHelper
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param EncryptorInterface $encryptor
     * @param ClientFactory $clientFactory
     * @param SerializerInterface $serializer
     * @param Context $context
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ClientFactory $clientFactory,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->clientFactory = $clientFactory;
        $this->serializer = $serializer;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @param Payment $payment
     * @param array $creditData
     * @return array|mixed
     * @throws CommandException
     */
    public function tokenization($payment, $creditData)
    {
        $accessToken = $this->requestToken($payment);
        $methodInstance = $payment->getMethodInstance();
        /** @var Client $client */
        $client = $this->clientFactory->create();
        $client->setMethod('POST');
        $data = [
            'tokenization' => true,
            'records' => [
                [
                    'fields' => $creditData
                ]
            ]
        ];
        $client->setRawBody($this->serializer->serialize($data));

        $url = trim($methodInstance->getConfigData('sf_vault_url'), "/").
            '/v1/vaults/'.
            $methodInstance->getConfigData('sf_vault_id').
            '/cards';

        $client->setUri($url);

        $client->setEncType('application/json');

        $client->setHeaders([
            "Authorization: Bearer " . $accessToken,
            "Content-type: application/json",
            "Accept: application/json",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36"
        ]);

        try {
            $response = $client->send();
            $body = $this->serializer->unserialize($response->getBody());
            if (isset($body['records'][0]['skyflow_id'])) {
                return $body['records'][0];
            }
            $this->logger->error($response->getBody());
        } catch (\Exception $exception) {
            $this->logger->error($client->getResponse()->getBody());
            $this->logger->error($exception->getMessage());
        }

        throw new CommandException(__("Cannot process Tonder Payment due to unexpected error. Please contact administrator for further support"));
    }

    /**
     * @param Payment $payment
     * @return string
     */
    protected function requestToken($payment)
    {
        $methodInstance = $payment->getMethodInstance();

        $sfData = [
            'clientID' => $methodInstance->getConfigData('sf_client_id'),
            'clientName' => $methodInstance->getConfigData('sf_client_name'),
            'tokenURI' => $methodInstance->getConfigData('sf_token_uri'),
            'keyID' => $methodInstance->getConfigData('sf_key_id'),
            'privateKey' => $methodInstance->getConfigData('sf_private_key'),
        ];

        $sfData['privateKey'] = str_replace("\\n", "\n", $sfData['privateKey']);

        $claims = [
            'iss' => $sfData['clientID'],
            'key' => $sfData['keyID'],
            'aud' => $sfData['tokenURI'],
            'exp' => time() + 3600,
            'sub' => $sfData['clientID'],
        ];

        $signedJwt = JWT::encode($claims, $sfData['privateKey'], 'RS256');

        $data = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $signedJwt
        ];

        $client = $this->clientFactory->create();
        $client->setMethod('POST');
        $client->setRawBody($this->serializer->serialize($data));

        $client->setUri($sfData['tokenURI']);

        try {
            $response = $client->send();
            $body = $this->serializer->unserialize($response->getBody());
            if (isset($body['accessToken'])) {
                return $body['accessToken'];
            }
            $this->logger->error($response->getBody());
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        throw new CommandException(__("Cannot process Tonder Payment due to incorrect system configuration. Please contact administrator for further support"));
    }
}