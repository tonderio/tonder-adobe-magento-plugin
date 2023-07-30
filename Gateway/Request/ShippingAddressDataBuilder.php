<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;

/**
 * Class ShippingAddressDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class ShippingAddressDataBuilder extends AbstractDataBuilder
{
    const SHIPPING_ADDRESS = 'shipping_address';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;


    /**
     * @param ConfigInterface $config
     * @param CountryFactory $countryFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        ConfigInterface $config,
        CountryFactory  $countryFactory,
        RegionFactory   $regionFactory
    )
    {
        $this->config = $config;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        /** @var Payment $payment */
        $order = $paymentDO->getOrder();
        $shippingAddress = $order->getShippingAddress();

        if (!$shippingAddress) {
            return [];
        }

        $countryCode = $shippingAddress->getCountryId();
        $regionCode = $shippingAddress->getRegionCode();
        $postalCode = $shippingAddress->getPostcode();
        return [
            self::SHIPPING_ADDRESS => [
                "street" => $shippingAddress->getStreetLine1() ?? "",
                "number" => 0,
                "suburb" => $this->getRegionName($regionCode, $countryCode) ?? 'none',
                "zip_code" => $this->cutPostalCode( $postalCode) ?? 0000,
                "country" => $this->getCountryName($countryCode) ?? "",
                "state" => $this->getRegionName($regionCode, $countryCode) ?? 'none',
                "city" => $shippingAddress->getCity() ?? "none"
            ]
        ];
    }


    private function cutPostalCode( $postalCode){
        return substr($postalCode, 0, 4);
    }
    /**
     * @param $countryCode
     * @return string
     */
    private function getCountryName($countryCode)
    {
        return $this->countryFactory->create()->loadByCode($countryCode)->getName();
    }

    /**
     * @param $regionCode
     * @param $countryCode
     * @return string
     */
    private function getRegionName($regionCode, $countryCode)
    {
        return $this->regionFactory->create()->loadByCode($regionCode,$countryCode)->getName();
    }
}
