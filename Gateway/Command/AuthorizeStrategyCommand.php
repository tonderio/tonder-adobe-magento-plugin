<?php
namespace Tonder\Payment\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;

class AuthorizeStrategyCommand implements CommandInterface
{
    /**
     * Tonder pre authorize command
     */
    const PRE_AUTH = 'pre_auth';

    /**
     * Tonder Vault Capture Command
     */
    const VAULT_AUTHORIZE = 'vault_authorize';

    /**
     * Tonder Verify Card Command
     */
    const VERIFY_CARD = 'verify_card';

    /**
     * Tonder Verify Card with Vault Command
     */
    const VERIFY_CARD_VAULT = 'vault_verify_card';

    const CHECK_KOUNT = 'check_kount';

    const DIRECT_TYPE = 'direct';

    /**
     * @var Command\CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @param CommandPoolInterface $commandPool
     * @param ConfigInterface $config
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        ConfigInterface $config
    ) {
        $this->commandPool = $commandPool;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentObject */
        $paymentObject = SubjectReader::readPayment($commandSubject);

        /** @var Order\Payment $payment */
        $payment = $paymentObject->getPayment();
        ContextHelper::assertOrderPayment($payment);


        $this->avsAndCvdCondition($commandSubject, $payment);

        if ($this->config->getValue('kount_enable') &&
            $this->config->getValue('connection_type') == self::DIRECT_TYPE) {
            $this->commandPool
                ->get(self::CHECK_KOUNT)
                ->execute($commandSubject);
        }

        if ($payment->getAdditionalInformation('public_hash')
            && empty($payment->getAdditionalInformation('cc_type'))) {
            return $this->commandPool
                ->get(self::VAULT_AUTHORIZE)
                ->execute($commandSubject);
        }

        return $this->commandPool
            ->get(self::PRE_AUTH)
            ->execute($commandSubject);
    }

    /**
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws Command\CommandException
     */
    public function avsAndCvdCondition($commandSubject, $payment)
    {
        if ($this->config->getValue('avs_enable') || $this->config->getValue('cvd_enable')) {
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
    }
}
