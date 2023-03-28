<?php
namespace Tonder\Payment\Gateway\Http;

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
        return $this->transferBuilder
            ->setMethod('POST')
            ->setBody($this->convertToXml($request))
            ->setUri($this->getUrl())
            ->build();
    }
}
