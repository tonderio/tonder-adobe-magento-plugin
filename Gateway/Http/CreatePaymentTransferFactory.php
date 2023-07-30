<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Http;

use Tonder\Payment\Gateway\Http\AbstractTransferFactory;

/**
 * Class CreatePaymentTransferFactory
 */
class CreatePaymentTransferFactory extends AbstractTransferFactory
{

    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        $url = $this->getUrl() . "business/". $request["business"] ."/payments/";
        unset($request["business"]);
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
