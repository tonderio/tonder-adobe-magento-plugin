<?php
namespace Tonder\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Class Complete
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Complete extends Action
{
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var Json
     */
    protected $_jsonFramework;

    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;
    /**
     * @var Order
     */
    protected $order;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param LoggerInterface $logger
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param ConfigInterface $config
     * @param Order $order
     * @param Json $_jsonFramework
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        LoggerInterface $logger,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        ConfigInterface $config,
        Order $order,
        Json $_jsonFramework
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->logger = $logger;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->order = $order;
    }

    /**
     * @return ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $params = $this->getRequest()->getParams();
            if ($this->config->getValue('debug')) {
                $this->logger->debug('Response: ' . $this->_jsonFramework->serialize($params));
            }
            if (!isset($params['response_order_id'])) {
                return $this->processException($resultRedirect, __('Could not find Order Id'));
            }
            $pattern = "/-r[0-9]{2}$/";
            $orderIncrementId = preg_replace($pattern, '', $params['response_order_id']);
            $params['response_order_id'] = $orderIncrementId;
            $order = $this->order->loadByIncrementId($orderIncrementId);
            if (!$order->getId()) {
                return $this->processException($resultRedirect, __('We can\'t find Order Id'));
            }
            $this->orderPayment($order, $params);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $this->messageManager->addError($e->getMessage());
            $resultRedirect->setPath('tonder/order/cancel');
            return $resultRedirect;
        }

        $resultRedirect->setPath('checkout/onepage/success');

        return $resultRedirect;
    }

    /**
     * @param $resultRedirect
     * @param $message
     * @return mixed
     */
    protected function processException($resultRedirect, $message)
    {
        $this->logger->debug($message);
        $this->messageManager->addError($message);
        $resultRedirect->setPath('tonder/order/cancel');
        return $resultRedirect;
    }

    /**
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    protected function orderPayment($order, $params)
    {
        $payment = $order->getPayment();
        $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);
        $arguments['response'] = $params;

        $this->commandPool->get('complete')->execute($arguments);
    }
}
