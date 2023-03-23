<?php

namespace Tonder\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * Forward the tonder order_id to create_charge API call
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);

        $paymentInfo = $method->getInfoInstance();
        if ($data->getDataByKey('transaction_result') !== null) {
            $paymentInfo->setAdditionalInformation(
                'transaction_result',
                $data->getDataByKey('transaction_result')
            );
        }
        if ($data->getAdditionalData()) {
            $additionalData = $data->getAdditionalData();
            if (isset($additionalData['order_id'])) {
                $paymentInfo->setAdditionalInformation(
                    'order_id',
                    $additionalData['order_id']
                );
            }
            if (isset($additionalData['order_id'])) {
                $paymentInfo->setAdditionalInformation(
                    'anti_fraud_metadata',
                    $additionalData['anti_fraud_metadata']
                );
                if (isset($additionalData['charge_additional_details'])) {
                    $paymentInfo->setAdditionalInformation(
                        'charge_additional_details',
                        $additionalData['charge_additional_details']
                    );
                }
                if (isset($additionalData['tonder_user_language'])) {
                    $paymentInfo->setAdditionalInformation(
                        'tonder_user_language',
                        $additionalData['tonder_user_language']
                    );
                }
            }
        }
    }
}
