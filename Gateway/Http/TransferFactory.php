<?php
namespace Tonder\Payment\Gateway\Http;

use Tonder\Payment\Gateway\Request\AbstractDataBuilder;

/**
 * Class TransferFactory
 */
class TransferFactory extends AbstractTransferFactory
{

    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setMethod('POST')
            ->setBody($this->serializer->serialize($request))
            ->setHeaders([
                "Authorization: Basic " . base64_encode(implode(":", $this->getCredentials())),
                "Content-type: application/json",
            ])
            ->setUri($this->getUrl()."checkout-router/")
            ->build();
    }
}
