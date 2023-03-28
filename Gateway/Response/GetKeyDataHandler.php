<?php
namespace Tonder\Payment\Gateway\Response;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\PaymentTokenFactory;

/**
 * Vault Details Handler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetKeyDataHandler implements HandlerInterface
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var OrderPaymentExtension
     */
    protected $orderPaymentExtension;

    /**
     * @var PaymentTokenFactory
     */
    protected $tokenFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    protected $tokenManagement;

    /**
     * Constructor
     *
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param EncryptorInterface $encryptor
     * @param OrderPaymentExtension $orderPaymentExtension
     * @param PaymentTokenFactory $tokenFactory
     * @param PaymentTokenManagementInterface $tokenManagement
     */
    public function __construct(
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        EncryptorInterface $encryptor,
        OrderPaymentExtension $orderPaymentExtension,
        PaymentTokenFactory $tokenFactory,
        PaymentTokenManagementInterface $tokenManagement
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->encryptor = $encryptor;
        $this->orderPaymentExtension = $orderPaymentExtension;
        $this->tokenFactory = $tokenFactory;
        $this->tokenManagement = $tokenManagement;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($response, $handlingSubject);
        $paymentToken->setCustomerId($handlingSubject['cust_id']);
        $paymentToken->setIsActive(true);
        $paymentToken->setPaymentMethodCode('moneris');
        $paymentToken->setIsVisible(true);
        $paymentToken->setPublicHash($this->generatePublicHash($paymentToken));

        return $this->saveDataKey($paymentToken);
    }

    /**
     * Save vault payment token entity
     *
     * @param $paymentToken
     * @return true|false
     */
    public function saveDataKey(PaymentTokenInterface $paymentToken)
    {
        $vaultPaymentToken = $this->getVaultPaymentTokenModel($paymentToken);
        $vaultPaymentToken->addData($paymentToken->getData());
        try {
            $vaultPaymentToken->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getVaultPaymentTokenModel(PaymentTokenInterface $paymentToken)
    {
        $vault = $this->tokenManagement->getByPublicHash($paymentToken->getPublicHash(), $paymentToken->getCustomerId());
        if (!$vault) {
            $vault = $this->tokenFactory->create();
        }
        return $vault;
    }

    /**
     * Get vault payment token entity
     *
     * @param $response
     * @param $handlingSubject
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken($response, $handlingSubject)
    {
        if (isset($response['DataKey'])) {
            $token = $response['DataKey'];
        } else {
            return null;
        }
        $paymentToken = $this->paymentTokenFactory->create();
        $expirationDate = $this->getExpirationDate($handlingSubject['year'], $handlingSubject['month']);
        $paymentToken->setGatewayToken($token);
        $paymentToken->setExpiresAt($expirationDate);
        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'type' => $handlingSubject['type'],
            'maskedCC' => substr($handlingSubject['pan'], -4),
            'expirationDate' => $handlingSubject['month'] . '/' . $handlingSubject['year']
        ]));

        return $paymentToken;
    }

    /**
     * @param $year
     * @param $month
     * @return string
     * @throws \Exception
     */
    private function getExpirationDate($year, $month)
    {
        $expDate = new \DateTime(
            $year
            . '-'
            . $month
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Generate vault payment public hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    protected function generatePublicHash(PaymentTokenInterface $paymentToken)
    {
        $hashKey = $paymentToken->getGatewayToken();
        if ($paymentToken->getCustomerId()) {
            $hashKey = $paymentToken->getCustomerId();
        }

        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();

        return $this->encryptor->getHash($hashKey);
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
    }
}
