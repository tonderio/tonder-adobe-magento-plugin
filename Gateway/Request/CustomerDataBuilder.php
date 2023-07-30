<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
/**
 * Class CustomerDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class CustomerDataBuilder extends AbstractDataBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order          = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        if (!$billingAddress) {
            return [];
        }
        $phone = $this->onlyNumbers($billingAddress->getTelephone());
        return [
            'name' => $billingAddress->getFirstname(),
            'last_name' => $billingAddress->getLastname(),
            'email_client' => $billingAddress->getEmail(),
            'phone' => $this->removeSpecialCharacterInPhone( $phone)
        ];
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
