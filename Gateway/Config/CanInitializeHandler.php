<?php
namespace Tonder\Payment\Gateway\Config;

use Tonder\Payment\Model\Adminhtml\Source\ConnectionType;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;

/**
 * Class CanInitializeHandler
 */
class CanInitializeHandler implements ValueHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $subject, $storeId = null)
    {
        switch ($this->config->getValue('connection_type', $storeId)) {
            case ConnectionType::CONNECTION_TYPE_DIRECT:
                if ($this->config->getValue('three_d_secure', $storeId)) {
                    $paymentObject = SubjectReader::readPayment($subject);

                    /** @var Order\Payment $payment */
                    $payment   = $paymentObject->getPayment();
                    $canUse3DS = $payment->getAdditionalInformation('can_use_3ds');
                    if ($canUse3DS) {
                        return 1;
                    }
                }
                return 0;
            default:
                return 1;
        }
    }
}
