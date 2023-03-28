<?php
/**
 * Created by PhpStorm.
 * User: crist
 * Date: 20/02/2019
 * Time: 08:56
 */

namespace Tonder\Payment\Gateway\Helper;

use Tonder\Payment\Gateway\Request\AmountDataBuilder;
use Tonder\Payment\Gateway\Request\MerchantDataBuilder;
use Tonder\Payment\Gateway\Request\TransactionDataBuilder;
use Tonder\Payment\Gateway\Request\TransactionIdDataBuilder;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

class RefundHelper
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    protected $data;

    /**
     * @var TransferFactoryInterface
     */
    protected $transfer;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * RefundHelper constructor.
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param ConfigInterface $config
     */
    public function __construct(
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        ConfigInterface $config
    ) {
        $this->transfer = $transferFactory;
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @return array
     */
    protected function prepareMerchant()
    {
        return [
            MerchantDataBuilder::STORE_ID => $this->config->getValue(MerchantDataBuilder::STORE_ID),
            MerchantDataBuilder::API_TOKEN => $this->config->getValue(MerchantDataBuilder::API_TOKEN)
        ];
    }

    /**
     * @return array[]
     */
    protected function prepareTransactionId()
    {
        return [
            TransactionIdDataBuilder::REPLACE_KEY => [
                TransactionIdDataBuilder::TRANSACTION_ID => $this->data['TransID']
            ]
        ];
    }

    /**
     * @return array[]
     */
    protected function prepareTransaction()
    {
        return [
            TransactionDataBuilder::REPLACE_KEY => [
                TransactionDataBuilder::ORDER_ID =>$this->data['ReceiptId'],
                TransactionDataBuilder::CRYPT_TYPE => '7', //TODO change it
            ]
        ];
    }

    /**
     * @return array[]
     */
    protected function prepareAmount()
    {
        return [
            AmountDataBuilder::REPLACE_KEY => [
                AmountDataBuilder::AMOUNT => sprintf('%.2F', $this->data['TransAmount'])
            ]
        ];
    }

    /**
     * @param $data
     */
    public function setResponse($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    protected function buildRefundRequest()
    {
        $result = [];
        $result = $this->merge($result, $this->prepareMerchant());
        $result = $this->merge($result, $this->prepareTransactionId());
        $result = $this->merge($result, $this->prepareTransaction());
        $result = $this->merge($result, $this->prepareAmount());
        return $result;
    }

    /**
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function refund()
    {
        $data = $this->buildRefundRequest();
        $transfer = $this->transfer->create($data);
        $this->client->placeRequest($transfer);
    }

    /**
     * @param array $result
     * @param array $builder
     * @return array
     */
    protected function merge(array $result, array $builder)
    {
        return array_replace_recursive($result, $builder);
    }
}
