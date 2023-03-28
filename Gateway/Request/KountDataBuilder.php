<?php

namespace Tonder\Payment\Gateway\Request;

use DateTime;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Session\SessionManager;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;

/**
 * Class CustomerDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class KountDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const KOUNT_INQUIRY = 'kount_inquiry';
    const AVS_NO_MATCH = ['N','P','Z','T','W','A','B','S','A','B','R','S','U'];
    const CVD_NO_MATCH = ['1N','1D'];
    const CVD_UNAVAILABLE = ['1P','1S','1U','Other'];

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * @var CollectionFactory
     */
    protected $paymentTokenCollectionFactory;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * CVDDataBuilder constructor.
     * @param EncryptorInterface $encryptor
     * @param ConfigInterface $config
     * @param SessionManager $sessionManager
     * @param CollectionFactory $collectionFactory
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ConfigInterface $config,
        SessionManager $sessionManager,
        CollectionFactory $collectionFactory,
        RemoteAddress $remoteAddress
    ) {
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->sessionManager = $sessionManager;
        $this->paymentTokenCollectionFactory = $collectionFactory;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        if ($this->config->getValue('kount_enable')) {
            $paymentDO = SubjectReader::readPayment($buildSubject);
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $paymentDO->getPayment();
            ContextHelper::assertOrderPayment($payment);
            $order = $paymentDO->getPayment()->getOrder();
            $date = new DateTime();
            $kountData = [
                self::REPLACE_KEY => [
                        'kount_merchant_id' => $this->config->getValue('kount_merchant_id'),
                        'kount_api_key' => $this->config->getValue('kount_api_key'),
                        'order_id' => $order->getIncrementId(),
                        'call_center_ind' => 'N',
                        'payment_response' => 'A',
                        'currency' => $order->getOrderCurrencyCode(),
                        'email' => $order->getCustomerEmail(),
                        'customer_name' => $order->getCustomerFirstName() . ' ' . $order->getCustomerLastname(),
                        'payment_type' => 'CARD',
                        'ip_address' => $this->remoteAddress->getRemoteAddress(),
                        'session_id' => $this->sessionManager->getSessionId(),
                        'website_id' => $this->config->getValue('website_id'),
                        'amount' => sprintf('%.2F', $payment->getAmountOrdered()),
                        'epoc' => (string)$date->getTimestamp()
                    ]
            ];

            if ($order->getCustomerId()) {
                $kountData[self::REPLACE_KEY] += ['customer_id' => sprintf($order->getCustomerId())];
            }
            if (!empty($this->getPaymentToken($payment))) {
                $kountData[self::REPLACE_KEY] += $this->getPaymentToken($payment);
            }
            $additionalInformation = $payment->getAdditionalInformation();
            if (isset($additionalInformation['avs_response_code'])) {
                $kountData[self::REPLACE_KEY]['avs_response'] = !in_array($additionalInformation['avs_response_code'], self::AVS_NO_MATCH) ? 'M' : 'N';
            }
            if (isset($additionalInformation['cvd_response_code'])) {
                $kountData[self::REPLACE_KEY]['cvd_response'] = 'M';
                if (in_array($additionalInformation['cvd_response_code'], self::CVD_NO_MATCH)) {
                    $kountData[self::REPLACE_KEY]['cvd_response'] = 'N';
                } elseif (in_array($additionalInformation['cvd_response_code'], self::CVD_UNAVAILABLE)) {
                    $kountData[self::REPLACE_KEY]['cvd_response'] = 'X';
                }
            }

            $kountData[self::REPLACE_KEY] += $this->getProductInfo($order->getItems());
            if ($order->getBillingAddress()) {
                $kountData[self::REPLACE_KEY] += $this->getAddressInfo($order->getBillingAddress(), 'bill');
            }
            if ($order->getShippingAddress()) {
                $kountData[self::REPLACE_KEY] += $this->getAddressInfo($order->getShippingAddress(), 'ship');
            }
            return $kountData;
        }
        return [];
    }

    /**
     * @param $items
     * @return array
     */
    public function getProductInfo($items)
    {
        $productInfo = [];
        $count = 0;
        foreach ($items as $item) {
            if (!$item->getParentItem()) {
                $count++;
                $productInfo['prod_type_' . $count] = $item->getProductType();
                $productInfo['prod_item_' . $count] = $item->getName();
                $productInfo['prod_quant_' . $count] = number_format($item->getQtyOrdered(), 0);
                $productInfo['prod_price_' . $count] = sprintf('%.2F', $item->getPrice());
            }
        }
        return $productInfo;
    }

    /**
     * @param $address
     * @param $type
     * @return array
     */
    public function getAddressInfo($address, $type)
    {
        return [
            $type . '_country' => $address->getCountryId(),
            $type . '_city' => $address->getCity(),
            $type . '_street_1' => $address->getStreetLine(1),
            $type . '_phone' => $address->getTelephone(),
        ];
    }

    /**
     * @param $payment
     * @return array
     */
    public function getPaymentToken($payment)
    {
        if (in_array($payment->getAdditionalInformation(OrderPaymentInterface::CC_TYPE), ['VI', 'MC', 'DI', 'AE'])) {
            $cardNumberEncrypt = $payment->getAdditionalInformation()[OrderPaymentInterface::CC_NUMBER_ENC];
            return [
                'payment_token' => $this->encryptor->decrypt($cardNumberEncrypt)
            ];
        } elseif ($payment->getAdditionalInformation('public_hash')
            && empty($payment->getAdditionalInformation('cc_type'))) {
            $token = $this->paymentTokenCollectionFactory->create()
                ->addFieldToFilter('customer_id', $payment->getOrder()->getCustomerId())
                ->addFieldToFilter('public_hash', $payment->getAdditionalInformation('public_hash'))
                ->getFirstItem();
            if (!$token->getGatewayToken()) {
                throw new LocalizedException(__('Could not find token for this card. Please use a new one.'));
            }
            return [
                'payment_token' => '',
                'data_key' => $token->getGatewayToken()
            ];
        }
        return [];
    }
}
