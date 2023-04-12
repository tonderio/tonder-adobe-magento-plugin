<?php
namespace Tonder\Payment\Gateway\Http;

use Tonder\Payment\Gateway\Request\MerchantDataBuilder;
use Tonder\Payment\Gateway\Request\TransactionIdDataBuilder;

/**
 * Class RefundTransferFactory
 * @package Tonder\Payment\Gateway\Http
 */
class RefundTransferFactory extends AbstractTransferFactory
{
    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        $url = $this->getUrl();
//        $url = __($url, $request[MerchantDataBuilder::MERCHANT_ID], $request[TransactionIdDataBuilder::TRANSACTION_ID]);
        $url = __($url, 23, 23);
        unset($request[MerchantDataBuilder::MERCHANT_ID]);
        unset($request[TransactionIdDataBuilder::TRANSACTION_ID]);

        return $this->transferBuilder
            ->setMethod('POST')
            ->setBody($this->serializer->serialize($request))
            ->setHeaders([
                "Authorization: Basic " . base64_encode(implode(":", $this->getCredentials())),
                "Content-type: application/json",
            ])
            ->setUri($url)
            ->build();
    }
}
