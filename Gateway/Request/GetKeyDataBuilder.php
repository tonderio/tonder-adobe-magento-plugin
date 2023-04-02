<?php
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TransactionDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class GetKeyDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const TYPE = 'res_add_cc';

    const CUST_ID = 'cust_id';

    const EMAIL = 'email';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $type = self::TYPE;
        if ($buildSubject['isUs'] == "true") {
            $type = 'us_' . self::TYPE;
        }
        return [
            MerchantDataBuilder::STORE_ID => $buildSubject[MerchantDataBuilder::STORE_ID],
            MerchantDataBuilder::API_TOKEN => $buildSubject[MerchantDataBuilder::API_TOKEN],
            $type => [
                self::CUST_ID => $buildSubject[self::CUST_ID],
                self::EMAIL => $buildSubject[self::EMAIL],
                TransactionDataBuilder::CRYPT_TYPE => '7',
            ]
        ];
    }
}
