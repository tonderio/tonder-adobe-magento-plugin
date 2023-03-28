<?php
namespace Tonder\Payment\Gateway\Http;

/**
 * Class VoidTransferFactory
 * @package Tonder\Payment\Gateway\Http
 */
class VoidTransferFactory extends AbstractTransferFactory
{
    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setMethod('POST')
            ->setBody($this->convertToXml($request))
            ->setUri($this->getUrl())
            ->build();
    }
}
