<?php

namespace Tonder\Payment\Gateway\Request\Vault;

use Tonder\Payment\Gateway\Request\AbstractDataBuilder;
use Tonder\Payment\Gateway\Request\DataBuilder;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;

class VerificationCardTransactionDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const DATA_KEY = 'data_key';

    const ORDER_ID = 'order_id';

    const CRYPT_TYPE = 'crypt_type';

    /**
     * @var DataBuilder
     */
    protected $dataBuilder;

    /**
     * VaultDetailsDataBuilder constructor.
     * @param DataBuilder $dataBuilder
     */
    public function __construct(
        \Tonder\Payment\Gateway\Request\DataBuilder $dataBuilder
    ) {
        $this->dataBuilder = $dataBuilder;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $buildSubject)
    {
        list($token, $order) = $this->dataBuilder->buildData($buildSubject);
        return [
            self::REPLACE_KEY => [
                self::DATA_KEY => $token->getGatewayToken(),
                self::ORDER_ID => "VerifyCard-" . $order->getOrderIncrementId() . "-" . time(),
                self::CRYPT_TYPE => '1',
            ]
        ];
    }
}
