<?php
/**
 * Copyright © Tonder JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 11/05/2020
 * Time: 14:42
 */

namespace Tonder\Payment\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Command\CommandPoolInterface;

class VerifyCardStrategyCommand implements CommandInterface
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
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * VerifyCardStrategyCommand constructor.
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
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentObject */
        $paymentObject = SubjectReader::readPayment($commandSubject);

        /** @var Order\Payment $payment */
        $payment = $paymentObject->getPayment();
        ContextHelper::assertOrderPayment($payment);

        return $this->commandPool
            ->get(self::VERIFY_CARD)
            ->execute($commandSubject);
    }
}
