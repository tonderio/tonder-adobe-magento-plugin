<?php

namespace Tonder\Payment\Gateway\Command\Net;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Tonder\Payment\Gateway\Helper\ResponseReader;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class UpdateDetailsCommand
 */
class UpdateDetailsCommand implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var HandlerInterface
     */
    private $handler;
    /**
     * @var ValidatorInterface
     */
    private $validatorUS;

    /**
     * @var HandlerInterface
     */
    private $handlerUS;

    /**
     * @var ResponseReader
     */
    private $responseReader;

    /**
     * UpdateDetailsCommand constructor.
     * @param ConfigInterface $config
     * @param ValidatorInterface $validator
     * @param HandlerInterface $handler
     * @param ValidatorInterface $validatorUS
     * @param HandlerInterface $handlerUS
     * @param ResponseReader $responseReader
     */
    public function __construct(
        ConfigInterface $config,
        ValidatorInterface $validator,
        HandlerInterface $handler,
        ValidatorInterface $validatorUS,
        HandlerInterface $handlerUS,
        ResponseReader $responseReader
    ) {
        $this->config = $config;
        $this->validator = $validator;
        $this->handler = $handler;
        $this->validatorUS = $validatorUS;
        $this->handlerUS = $handlerUS;
        $this->responseReader = $responseReader;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);
        $response = $this->responseReader->readResponse($commandSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        if ($this->isUsCountry() == 1) {
            if ($this->validatorUS) {
                $result = $this->validatorUS->validate(
                    [
                        'payment' => $paymentDO,
                        'response' => $response
                    ]
                );
                if (!$result->isValid()) {
                    throw new CommandException(
                        __(implode("\n", $result->getFailsDescription()))
                    );
                }
            }

            if ($this->handlerUS) {
                $this->handlerUS->handle($commandSubject, $response);
            }
        } else {
            if ($this->validator) {
                $result = $this->validator->validate(
                    [
                        'payment' => $paymentDO,
                        'response' => $response
                    ]
                );
                if (!$result->isValid()) {
                    throw new CommandException(
                        __(implode("\n", $result->getFailsDescription()))
                    );
                }
            }

            if ($this->handler) {
                $this->handler->handle($commandSubject, $response);
            }
        }
    }

    /**
     * @return bool
     */
    public function isUsCountry()
    {
        if ($this->config->getValue('environment') == 'US') {
            return true;
        }

        return false;
    }
}
