<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Tonder\Payment\Logger\Logger;

class CaptureStrategyCommand implements CommandInterface
{
    const CUSTOMER_EXIST= "customer_exist";

    const CREATE_CUSTOMER = "create_customer";

    const CREATE_ORDER = "create_order";

    const CREATE_PAYMENT = "create_payment";
    const SALE = 'sale';

    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param CommandPoolInterface $commandPool
     * @param ConfigInterface $config
     * @param Logger $logger
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        ConfigInterface $config,
        Logger $logger
    ) {
        $this->commandPool = $commandPool;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        $this->logger->info('Capture Command');
        /** @var PaymentDataObjectInterface $paymentObject */
        $paymentObject = SubjectReader::readPayment($commandSubject);

        /** @var Order\Payment $payment */
        $payment = $paymentObject->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setAmountAuthorized($payment->getOrder()->getTotalDue());
        $payment->setBaseAmountAuthorized($payment->getOrder()->getBaseTotalDue());
        $payment->getOrder()->setCanSendNewEmailFlag(false);

        $payment->getOrder()->setState( Order::STATE_PENDING_PAYMENT);
        $payment->getOrder()->setStatus(Order::STATE_PENDING_PAYMENT);

        $this->logger->info('CUSTOMER_EXIST');
        $this->commandPool->get(self::CUSTOMER_EXIST)->execute($commandSubject);
        $this->logger->info('CREATE_CUSTOMER');
        $this->commandPool->get(self::CREATE_CUSTOMER)->execute($commandSubject);
        $this->logger->info('CREATE_ORDER');
        $this->commandPool->get(self::CREATE_ORDER)->execute($commandSubject);
        $this->logger->info('CREATE_PAYMENT');
        $this->commandPool->get(self::CREATE_PAYMENT)->execute($commandSubject);
        $this->logger->info('SALE');
        $this->commandPool->get(self::SALE)->execute($commandSubject);
        $this->logger->info('End Capture');
        return $this;
    }
}
