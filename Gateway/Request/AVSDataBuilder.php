<?php
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\ConfigInterface;

class AVSDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const AVS_INFO = 'avs_info';

    const AVS_STREET_NUMBER = 'avs_street_number';
    const AVS_STREET_NAME = 'avs_street_name';
    const AVS_ZIP_CODE = 'avs_zipcode';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * AVSDataBuilder constructor.
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        if (!$this->config->getValue('avs_enable')) {
            return [];
        }
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        if (!$billingAddress || !$billingAddress->getStreetLine1() || !$billingAddress->getPostcode()) {
            return [];
        }

        //Street number and name handler
        $streetNumber = '';
        $streetName = '';
        foreach (preg_split('/[ ,]/', $billingAddress->getStreetLine1()) as $result) {
            if (!$result) {
                continue;
            }
            if (is_numeric($result) || is_numeric($result[0])) {
                $streetNumber .= empty($streetNumber) ? $result : '';
            }
            if (ctype_alpha($result)) {
                $streetName .= empty($streetName) ? $result : '';
            }
        }
        $streetNumber = empty($streetNumber) ? '0' : $streetNumber;
        $streetName = empty($streetName) ? 'Street' : $streetName;

        return [
            self::REPLACE_KEY => [
                self::AVS_INFO => [
                    self::AVS_STREET_NAME => $streetName,
                    self::AVS_STREET_NUMBER => $streetNumber,
                    self::AVS_ZIP_CODE => $billingAddress->getPostcode()
                ]
            ]
        ];
    }
}
