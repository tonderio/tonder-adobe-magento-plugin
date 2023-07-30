<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Http;

use Tonder\Payment\Gateway\Request\MerchantDataBuilder;
use Tonder\Payment\Gateway\Request\TransactionIdDataBuilder;
use Tonder\Payment\Gateway\Http\AbstractTransferFactory;

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
        $url = __($url, $request[MerchantDataBuilder::MERCHANT_ID], $request[TransactionIdDataBuilder::TRANSACTION_CHARGE]);
        unset($request[MerchantDataBuilder::MERCHANT_ID]);
        unset($request[TransactionIdDataBuilder::TRANSACTION_ID]);
        unset($request[TransactionIdDataBuilder::TRANSACTION_CHARGE]);

        return $this->transferBuilder
            ->setMethod('POST')
            ->setBody($this->serializer->serialize($request))
            ->setHeaders([
                "Authorization: Token " . $this->getToken(),
                "Content-type: application/json",
            ])
            ->setUri($url)
            ->build();
    }
}
