<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Http;

use Tonder\Payment\Gateway\Http\AbstractTransferFactory;
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
