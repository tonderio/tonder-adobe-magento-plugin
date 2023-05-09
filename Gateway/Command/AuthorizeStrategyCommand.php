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
     * Tonder pre-authorize command
     */
    const PRE_AUTH = 'pre_auth';

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

        return $this->commandPool
            ->get(self::PRE_AUTH)
            ->execute($commandSubject);
    }
}
