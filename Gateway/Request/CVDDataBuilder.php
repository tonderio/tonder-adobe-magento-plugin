<?php
namespace Tonder\Payment\Gateway\Request;

use Tonder\Payment\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class CustomerDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class CVDDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const CVD_INFO = 'cvd_info';

    const CVD_INDICATOR = 'cvd_indicator';
    const CVD_VALUE = 'cvd_value';

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * CVDDataBuilder constructor.
     * @param EncryptorInterface $encryptor
     * @param ConfigInterface $config
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ConfigInterface $config
    ) {
        $this->config = $config;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        if (!$this->config->getValue('cvd_enable')) {
            return [];
        }
        $paymentDO = SubjectReader::readPayment($buildSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $data = $payment->getAdditionalInformation();
        if (!isset($data[DataAssignObserver::CC_CID_ENC])) {
            return [];
        }

        return [
            self::REPLACE_KEY => [
                self::CVD_INFO => [
                    self::CVD_INDICATOR => "1",
                    self::CVD_VALUE => $this->encryptor->decrypt($data[DataAssignObserver::CC_CID_ENC])
                ]
            ]
        ];
    }
}
