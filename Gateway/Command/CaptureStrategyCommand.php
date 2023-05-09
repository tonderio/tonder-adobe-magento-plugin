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

        return $this->commandPool
            ->get(self::SALE)
            ->execute($commandSubject);
    }
}
