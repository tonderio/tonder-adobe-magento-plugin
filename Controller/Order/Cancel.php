<?php
namespace Tonder\Payment\Controller\Order;

use Tonder\Payment\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Webapi\Exception;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\OrderRepositoryInterface;

class Cancel extends Action
{
    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var array
     */
    private $testData = [
        'response_order_id' =>  000000006,
        ''
    ];
    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * Cancel constructor.
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param Logger $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        Logger $logger,
        OrderRepositoryInterface $orderRepository,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        Session $checkoutSession,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $cancelResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $orderId = $this->checkoutSession->getData('last_order_id');
        try {
            if (!is_numeric($orderId)) {
                $orderId = $this->getRequest()->getParam('order_id');
                if (!$orderId) {
                    $cancelResult->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
                    $cancelResult->setData(['message' => __('Sorry, but something went wrong')]);
                    return $cancelResult;
                }
            }
            $order = $this->orderRepository->get((int)$orderId);
            $message = $this->getRequest()->getParam('message');
            $this->redirectPage($order, $message);
            $payment = $order->getPayment();
            if ($payment) {
                ContextHelper::assertOrderPayment($payment);
                $paymentDataObject = $this->paymentDataObjectFactory->create($payment);

                $commandResult = $this->commandPool->get('cancel_order')->execute(['payment' => $paymentDataObject]);

                $cancelResult->setData($commandResult->get());
            } else {
                throw new \Exception(__('Could not get Payment Data'));
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('checkout/cart/index');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $message
     */
    private function redirectPage($order, $message = '')
    {
        $quoteId = $order->getQuoteId();
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $this->_customerSession->setIsLoggedIn(true);
            $this->_customerSession->setCustomerId($customerId);
        }
        $this->checkoutSession->setLastQuoteId($quoteId);
        $this->checkoutSession->setLastSuccessQuoteId($quoteId);
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
        if (!empty($message)) {
            $this->messageManager->addErrorMessage($message);
            $order->addCommentToStatusHistory($message);
        }
    }
}
