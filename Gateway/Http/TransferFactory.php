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
        if (isset($request[AbstractDataBuilder::REPLACE_KEY]) && isset($request[AbstractDataBuilder::REPLACE_KEY]['type'])) {
            if ($request[AbstractDataBuilder::REPLACE_KEY]['type'] == 'card_lookup'
               || $request[AbstractDataBuilder::REPLACE_KEY]['type'] == 'threeds_authentication'
               || $request[AbstractDataBuilder::REPLACE_KEY]['type'] == 'cavv_lookup'
           ) {
                return $this->transferBuilder
                   ->setMethod('POST')
                   ->setBody($this->convertToXml($request, $type = 'Mpi2Request'))
                   ->setUri($this->getUrl('', true))
                   ->build();
            }
        }
        return $this->transferBuilder
            ->setMethod('POST')
            ->setBody($this->convertToXml($request))
            ->setUri($this->getUrl())
            ->build();
    }
}
