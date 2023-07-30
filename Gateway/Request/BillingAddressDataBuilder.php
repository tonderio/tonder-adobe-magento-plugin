<?php
declare(strict_types=1);

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;

/**
 * Class BillingAddressDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class BillingAddressDataBuilder extends AbstractDataBuilder
{
    const BILLING_ADDRESS = "billing_address";

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
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        if (!$billingAddress) {
            return [];
        }
        $countryCode = $billingAddress->getCountryId();
        $regionCode = $billingAddress->getRegionCode();
        $postalCode = $billingAddress->getPostcode();
        return [
            self::BILLING_ADDRESS => [
                "street" => $billingAddress->getStreetLine1() ?? "",
                "number" => 0,
                "suburb" => $this->getRegionName($regionCode, $countryCode) ?? 'none',
                "zip_code" => $this->cutPostalCode( $postalCode) ?? 0000,
                "country" => $this->getCountryName($countryCode) ?? "",
                "state" => $this->getRegionName($regionCode, $countryCode) ?? 'none',
                "city" => $billingAddress->getCity() ?? "none"
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
