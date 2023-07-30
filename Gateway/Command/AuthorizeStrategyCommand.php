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
use Magento\Sales\Model\Order;
use Tonder\Payment\Logger\Logger;

class AuthorizeStrategyCommand implements CommandInterface
{
    const CUSTOMER_EXIST= "customer_exist";

    const CREATE_CUSTOMER = "create_customer";

    const CREATE_ORDER = "create_order";

    const CREATE_PAYMENT = "create_payment";
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
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute(array $commandSubject)
    {
        $this->logger->info('AuthorizeStrategyCommand');
        /** @var PaymentDataObjectInterface $paymentObject */
        $paymentObject = SubjectReader::readPayment($commandSubject);

        /** @var Order\Payment $payment */
        $payment = $paymentObject->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $this->logger->info('End AuthorizeStrategyCommand');
        $this->logger->info('CUSTOMER_EXIST');
        $this->commandPool->get(self::CUSTOMER_EXIST)->execute($commandSubject);
        $this->logger->info('CREATE_CUSTOMER');
        $this->commandPool->get(self::CREATE_CUSTOMER)->execute($commandSubject);
        $this->logger->info('CREATE_ORDER');
        $this->commandPool->get(self::CREATE_ORDER)->execute($commandSubject);
        $this->logger->info('CREATE_PAYMENT');
        $this->commandPool->get(self::CREATE_PAYMENT)->execute($commandSubject);
        //$this->commandPool->get(self::PRE_AUTH)->execute($commandSubject);
        return $this;
    }
}
