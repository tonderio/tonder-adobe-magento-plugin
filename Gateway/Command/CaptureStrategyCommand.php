<?php

namespace Tonder\Payment\Gateway\Command;

use Tonder\Payment\Model\Adminhtml\Source\ConnectionType;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;

class CaptureStrategyCommand extends AuthorizeStrategyCommand
{
    /**
     * Tonder Direct sale command
     */
    const SALE = 'sale';

    /**
     * Tonder Direct capture command
     */
    const PRE_AUTH_CAPTURE = 'pre_auth_capture';

    /**
     * Tonder Vault Capture Command
     */
    const VAULT_CAPTURE = 'vault_capture';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * CaptureStrategyCommand constructor.
     * @param CommandPoolInterface $commandPool
     * @param ConfigInterface $config
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        ConfigInterface $config,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
        parent::__construct($commandPool, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentObject */
        $paymentObject = SubjectReader::readPayment($commandSubject);

        /** @var Order\Payment $payment */
        $payment = $paymentObject->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $this->avsAndCvdCondition($commandSubject, $payment);

        if ($this->config->getValue('kount_enable')
            && $this->config->getValue('connection_type') == self::DIRECT_TYPE
            && !$payment->getAdditionalInformation('kount_transaction_id')) {
            $this->commandPool
                ->get(self::CHECK_KOUNT)
                ->execute($commandSubject);
        }


        if ($payment instanceof Order\Payment
            && $payment->getAuthorizationTransaction()
        ) {
            return $this->commandPool
                ->get(self::PRE_AUTH_CAPTURE)
                ->execute($commandSubject);
        }

        if ($payment->getAdditionalInformation('public_hash')
            && empty($payment->getAdditionalInformation('cc_type'))) {
            return $this->commandPool
                ->get(self::VAULT_CAPTURE)
                ->execute($commandSubject);
        }

        return $this->commandPool
            ->get(self::SALE)
            ->execute($commandSubject);
    }
}
