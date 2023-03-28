<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AbstractDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class MerchantDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * Store ID
     */
    const STORE_ID = 'store_id';

    /**
     * Api Token
     */
    const API_TOKEN = 'api_token';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * AbstractDataBuilder constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $storeId = $buildSubject['payment']->getPayment()->getOrder()->getStoreId();
        return [
            self::STORE_ID => $this->config->getValue(self::STORE_ID, $storeId),
            self::API_TOKEN => $this->config->getValue(self::API_TOKEN, $storeId)
        ];
    }
}
