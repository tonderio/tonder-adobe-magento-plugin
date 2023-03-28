<?php
namespace Tonder\Payment\Gateway\Command;

use Tonder\Payment\Model\Adminhtml\Source\ConnectionType;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;

class CavvStrategyCommand implements CommandInterface
{
    /**
     * Moneris Direct sale command
     */
    const SALE = 'sale';

    /**
     * Moneris cavv purchase command
     */
    const CAVV_PURCHASE = 'cavv_purchase';

    /**
     * Moneris cavv preauth command
     */
    const CAVV_PREAUTH = 'cavv_preauth';

    /**
     * Moneris cavv vault purchase command
     */
    const CAVV_VAULT_PURCHASE = 'cavv_vault_purchase';

    /**
     * Moneris cavv vault preauth command
     */
    const CAVV_VAULT_AUTHORIZE = 'cavv_vault_preauth';

    /**
     * Moneris Verify Card Command
     */
    const VERIFY_CARD = 'verify_card';

    /**
     * Moneris Verify Card with Vault Command
     */
    const VERIFY_CARD_VAULT = 'vault_verify_card';

    const CHECK_KOUNT = 'check_kount';

    /**
     * @var Command\CommandPoolInterface
     */
    private $commandPool;

    /** @var ConfigInterface  */
    private $config;

    /**
     * CavvStrategyCommand constructor.
     * @param Command\CommandPoolInterface $commandPool
     * @param ConfigInterface $config
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool,
        ConfigInterface $config
    ) {
        $this->commandPool = $commandPool;
        $this->config = $config;
    }

    /**
     * @param array $commandSubject
     * @return Command\ResultInterface|null
     * @throws Command\CommandException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentObject */
        $paymentObject = SubjectReader::readPayment($commandSubject);

        /** @var Order\Payment $payment */
        $payment = $paymentObject->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $avsEnable = $this->config->getValue('avs_enable');
        $cvdEnable = $this->config->getValue('cvd_enable');
        $threeDSecureEnable = $this->config->getValue('three_d_secure');
        $connectType = $this->config->getValue('connection_type');

        if ($threeDSecureEnable && $connectType == ConnectionType::CONNECTION_TYPE_DIRECT) {
            if (($avsEnable || $cvdEnable)) {
                if (in_array($payment->getAdditionalInformation('cc_type'), ['VI', 'MC', 'DI', 'AE'])) {
                    $this->commandPool
                        ->get(self::VERIFY_CARD)
                        ->execute($commandSubject);
                } elseif ($payment->getAdditionalInformation('public_hash')
                    && empty($payment->getAdditionalInformation('cc_type'))) {
                    $this->commandPool
                        ->get(self::VERIFY_CARD_VAULT)
                        ->execute($commandSubject);
                }
            }
            if ($this->config->getValue('kount_enable')
                && $this->config->getValue('connection_type') == ConnectionType::CONNECTION_TYPE_DIRECT
                && !$payment->getAdditionalInformation('kount_transaction_id')) {
                $this->commandPool
                    ->get(self::CHECK_KOUNT)
                    ->execute($commandSubject);
            }
            $paymentAction = $payment->getMethodInstance()->getConfigPaymentAction();

            if ($paymentAction == 'authorize') {
                if ($payment->getAdditionalInformation('public_hash')) {
                    return $this->commandPool
                        ->get(self::CAVV_VAULT_AUTHORIZE)
                        ->execute($commandSubject);
                }
                return $this->commandPool
                    ->get(self::CAVV_PREAUTH)
                    ->execute($commandSubject);
            } else {
                if ($payment->getAdditionalInformation('public_hash')) {
                    return $this->commandPool
                        ->get(self::CAVV_VAULT_PURCHASE)
                        ->execute($commandSubject);
                }
                return $this->commandPool
                    ->get(self::CAVV_PURCHASE)
                    ->execute($commandSubject);
            }
        }
        return $this->commandPool
            ->get(self::SALE)
            ->execute($commandSubject);
    }
}
