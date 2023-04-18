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
                "Authorization: Token " . $this->getToken(),
                "Content-type: application/json",
            ])
            ->setUri($this->getUrl())
            ->build();
    }
}
