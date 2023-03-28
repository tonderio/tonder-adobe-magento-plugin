<?php

namespace Tonder\Payment\Gateway\Command\Net;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CompleteCommand
 */
class CompleteCommand implements CommandInterface
{
    /**
     * @var UpdateDetailsCommand
     */
    private $updateDetailsCommand;

    /**
     * @var UpdateOrderCommand
     */
    private $updateOrderCommand;

    /**
     * @param UpdateDetailsCommand $updateDetailsCommand
     * @param UpdateOrderCommand $updateOrderCommand
     */
    public function __construct(
        UpdateDetailsCommand $updateDetailsCommand,
        UpdateOrderCommand $updateOrderCommand
    ) {
        $this->updateDetailsCommand = $updateDetailsCommand;
        $this->updateOrderCommand = $updateOrderCommand;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        $this->updateDetailsCommand->execute($commandSubject);
        $this->updateOrderCommand->execute($commandSubject);
    }
}
