<?php

namespace Tonder\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class CreateInvoice implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $_invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $_invoiceRepository;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Tonder\Payment\Model\TonderFactory
     */
    protected $_tonderFactory;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Tonder\Payment\Model\TonderFactory $tonderFactory
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Tonder\Payment\Model\TonderFactory $tonderFactory
    ) {
          $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
          $this->_invoiceService = $invoiceService;
          $this->_transactionFactory = $transactionFactory;
          $this->_invoiceRepository = $invoiceRepository;
          $this->_orderRepository = $orderRepository;
          $this->_tonderFactory = $tonderFactory;
    }

    /**
     * Observer execution
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order) {
            $orderId = $order->getId();
            $this->createInvoice($orderId);
        }
    }

    /**
     * Create invoice from order id
     *
     * @param int $orderId
     * @return \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Model\Order\Invoice|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createInvoice($orderId)
    {
        try {
            $order = $this->_orderRepository->get($orderId);
            if ($order) {
                if ($order->getTonderStatus()=="pending") {
                    $order->setStatus("pending");
                    $order->setState("new");
                    $order->save();
                    return null;
                }
                if ($order->getTonderStatus()=="pending_capture") {
                    $order->setStatus("pending_payment");
                    $order->setState(Order::STATE_PENDING_PAYMENT);
                    $order->save();
                    return null;
                }

                if ($order->getTonderStatus()!="success") {
                    return null;
                }
                $invoices = $this->_invoiceCollectionFactory->create()
                  ->addAttributeToFilter('order_id', ['eq' => $order->getId()]);

                $invoices->getSelect()->limit(1);

                if ((int)$invoices->count() !== 0) {
                    $invoices = $invoices->getFirstItem();
                    $invoice = $this->_invoiceRepository->get($invoices->getId());
                    return $invoice;
                }

                if (!$order->canInvoice()) {
                    return null;
                }
                $invoice = $this->_invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $invoice->setTransactionId($order->getTonderTransactionId())->setIsTransactionClosed(0);
                $order->addStatusHistoryComment(__('Automatically INVOICED'), false);
                $transactionSave = $this->_transactionFactory->create()
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
                $transactionSave->save();

                $tonder = $this->_tonderFactory->create()->load($order->getTonderOrderId(), 'tonder_order_id');
                $tonder->setOrderId($order->getId());
                $tonder->setInvoiceStatus(2)->save();

                return $invoice;
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
}
