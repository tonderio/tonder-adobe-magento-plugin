<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;

/**
 * Class TransactionDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class VaultDetailsDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const ACTION = 'res_purchase_cc';

    const DATA_KEY = 'data_key';

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
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        list($token, $order) = $this->dataBuilder->buildData($buildSubject);
        return [
            self::REPLACE_KEY => [
                self::DATA_KEY => $token->getGatewayToken(),
                GetKeyDataBuilder::CUST_ID => (string)$order->getCustomerId()
            ]
        ];
    }
}
