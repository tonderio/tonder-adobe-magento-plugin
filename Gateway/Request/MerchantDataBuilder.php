<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class AbstractDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class MerchantDataBuilder extends AbstractDataBuilder
{

    /**
     * Merchant ID
     */
    const MERCHANT_ID = 'merchant_id';

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
            self::MERCHANT_ID => $this->config->getValue(self::MERCHANT_ID, $storeId),
        ];
    }
}
