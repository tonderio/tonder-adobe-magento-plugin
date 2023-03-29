<?php
namespace Tonder\Payment\Controller\Order;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\ConfigInterface;

abstract class AbstractPostBack extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    /** @var \Magento\Checkout\Model\Session  */
    protected $checkoutSession;

    /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory  */
    protected $paymentDataObjectFactory;

    /** @var \Magento\Payment\Gateway\Command\CommandPoolInterface  */
    protected $commandPool;

    /** @var \Psr\Log\LoggerInterface  */
    protected $logger;

    /** @var ConfigInterface  */
    protected $config;
    /**
     * @var Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface
     */
    protected $orderStatusRepository;

    /**
     * PostBack constructor.
     *
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory
     * @param \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool
     * @param \Tonder\Payment\Logger\Logger $logger
     * @param ConfigInterface $config
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusRepository
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory,
        \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool,
        \Tonder\Payment\Logger\Logger $logger,
        ConfigInterface $config,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusRepository
    ) {
        $this->checkoutSession = $session;
        $this->_customerSession = $customerSession;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->commandPool = $commandPool;
        $this->logger = $logger;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        parent::__construct($context);
    }

    /**
     * @param $orderId
     * @return array|\Magento\Sales\Api\Data\OrderInterface
     * @throws InputException
     */
    protected function getOrder($orderId)
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * Return customer session object
     * @return \Magento\Customer\Model\Session
     */
    protected function _getCustomerSession()
    {
        return $this->_customerSession;
    }
}
