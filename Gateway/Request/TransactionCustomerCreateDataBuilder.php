<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CustomerDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class TransactionCustomerCreateDataBuilder extends AbstractDataBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $tonder_id = $payment->getAdditionalInformation("tonder_id");

        if($tonder_id !== 'null' && empty($tonder_id)){
            $order          = $paymentDO->getOrder();
            $billingAddress = $order->getBillingAddress();

            if (!$billingAddress) {
                return [];
            }

            $username = $billingAddress->getFirstname() . " " . $billingAddress->getLastname();
            $phone = $this->onlyNumbers($billingAddress->getTelephone());

            return [
                'username' => $username,
                'email' => $billingAddress->getEmail() ?? "",
                "password" => "",
                "repeat_password" => "",
                'phone' => $this->removeSpecialCharacterInPhone( $phone)
            ];
        }
        return [];
    }

    /**
     * @param $param
     * @return array|string|string[]|null
     */
    private function onlyNumbers($param){
        return preg_replace("/[^0-9]/", "", $param);
    }

    /**
     * @param $param
     * @return string
     */
    private function removeSpecialCharacterInPhone($param)
    {
        if (!empty($param)) {
            $caracters = preg_match("/^([+]).*$/", $param) ? '+' : '';
            return $caracters . preg_replace("/[^0-9]/", "", $param);
        }
        return $param;
    }
}
