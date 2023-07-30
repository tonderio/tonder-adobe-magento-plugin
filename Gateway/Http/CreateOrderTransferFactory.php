<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Http;

use Tonder\Payment\Gateway\Http\AbstractTransferFactory;

/**
 * Class CreateOrderTransferFactory
 */
class CreateOrderTransferFactory extends AbstractTransferFactory
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
            ->setUri($this->getUrl() )
            ->build();
    }
}
