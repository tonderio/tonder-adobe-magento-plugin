<?php

namespace Tonder\Payment\Helper;

use Firebase\JWT\JWT;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class SkyFlowProcessor extends AbstractHelper
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ZendClientFactory
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
     * @param ZendClientFactory $clientFactory
     * @param SerializerInterface $serializer
     * @param Context $context
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ZendClientFactory $clientFactory,
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
        /** @var ZendClient $client */
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
        $client->setRawData($this->serializer->serialize($data));

        $client->setUri(
            $this->encryptor->decrypt($methodInstance->getConfigData('sf_vault_url')).
            '/v1/vaults/'.
            $this->encryptor->decrypt($methodInstance->getConfigData('sf_vault_id')).
            '/credit_cards'
        );

        $client->setEncType('application/json');

        $client->setHeaders([
            "Authorization: Bearer " . $accessToken,
            "Content-type: application/json",
            "Accept: application/json",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36"
        ]);

        try {
            $response = $client->request();
            $body = $this->serializer->unserialize($response->getBody());
            if (isset($body['records'][0]['skyflow_id'])) {
                return $body['records'][0];
            }
            $this->logger->error($response->getBody());
        } catch (\Exception $exception) {
            $this->logger->error($client->getLastResponse()->getBody());
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
            'clientID' => $this->encryptor->decrypt($methodInstance->getConfigData('sf_client_id')),
            'clientName' => $this->encryptor->decrypt($methodInstance->getConfigData('sf_client_name')),
            'tokenURI' => $this->encryptor->decrypt($methodInstance->getConfigData('sf_token_uri')),
            'keyID' => $this->encryptor->decrypt($methodInstance->getConfigData('sf_key_id')),
            'privateKey' => $this->encryptor->decrypt($methodInstance->getConfigData('sf_private_key')),
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

        /** @var ZendClient $client */
        $client = $this->clientFactory->create();
        $client->setMethod('POST');
        $client->setRawData($this->serializer->serialize($data));

        $client->setUri($sfData['tokenURI']);

        try {
            $response = $client->request();
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