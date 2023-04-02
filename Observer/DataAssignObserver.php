<?php
namespace Tonder\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class DataAssignObserver
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    const CC_NUMBER = 'cc_number';
    const CC_CID = 'cc_cid';
    const CC_CID_ENC = 'cc_cid_enc';
    const CC_CARD_HOLDER = 'cc_card_holder';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::CC_NUMBER,
        self::CC_CID,
        self::CC_CARD_HOLDER,
        OrderPaymentInterface::CC_TYPE,
        OrderPaymentInterface::CC_EXP_MONTH,
        OrderPaymentInterface::CC_EXP_YEAR,
        'public_hash',
        'is_us'
    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            $value = $additionalData[$additionalInformationKey] ?? null;
            if ($additionalInformationKey == self::CC_CARD_HOLDER) {
                $value = $data->getData('additional_data/cc_card_holder_name');
                $paymentInfo->setAdditionalInformation(
                    self::CC_CARD_HOLDER,
                    $paymentInfo->encrypt($value)
                );

                continue;
            }
            if ($value === null) {
                continue;
            }

            if ($additionalInformationKey == self::CC_NUMBER) {
                $paymentInfo->setAdditionalInformation(
                    OrderPaymentInterface::CC_NUMBER_ENC,
                    $paymentInfo->encrypt($value)
                );

                continue;
            } elseif ($additionalInformationKey == self::CC_CID) {
                $paymentInfo->setAdditionalInformation(
                    self::CC_CID_ENC,
                    $paymentInfo->encrypt($value)
                );

                continue;
            }
            $paymentInfo->setAdditionalInformation(
                $additionalInformationKey,
                $value
            );
        }
    }
}
