<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Http;

use Tonder\Payment\Gateway\Request\TransactionCustomerExistDataBuilder;
use Tonder\Payment\Gateway\Http\AbstractTransferFactory;

/**
 * Class CustomerExistTransferFactory
 * @package Tonder\Payment\Gateway\Http
 */
class CustomerExistTransferFactory extends AbstractTransferFactory
{

    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        $url = $this->getUrl() . $request[TransactionCustomerExistDataBuilder::CUSTOMER_EMAIL];
        return $this->transferBuilder
            ->setMethod('GET')
            ->setBody($this->serializer->serialize($request))
            ->setHeaders([
                "Authorization: Token " . $this->getToken(),
                "Content-type: application/json",
            ])
            ->setUri($url)
            ->build();
    }
}
