<?php

namespace Tonder\Payment\Controller\Webhook;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Tonder\Payment\Helper\Data
     */
    protected $helper;

    /**
     * @var \Tonder\Payment\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Tonder\Payment\Model\TonderFactory
     */
    protected $tonderFactory;

    /**
     * @var \Tonder\Payment\Model\ResourceModel\Tonder\Collection
     */
    protected $tonderCollection;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var \Magento\Framework\Filesystem
     */
     protected $filesystem;

    /**
     * @param  \Magento\Framework\App\Action\Context                      $context
     * @param  \Magento\Framework\View\Result\PageFactory                 $resultPageFactory
     * @param  \Magento\Sales\Model\Service\InvoiceService                $invoiceService
     * @param  \Magento\Framework\DB\Transaction                          $dbTransaction
     * @param  \Tonder\Payment\Helper\Data                              $helper
     * @param  \Tonder\Payment\Logger\Logger                            $logger
     * @param  \Magento\Framework\Stdlib\DateTime\TimezoneInterface       $date
     * @param  \Magento\Sales\Model\Order                                 $order
     * @param  \Magento\Sales\Model\Order\CreditmemoFactory               $creditmemoFactory
     * @param  \Magento\Sales\Model\Order\Invoice                         $invoice
     * @param  \Magento\Sales\Model\Service\CreditmemoService             $creditmemoService
     * @param  \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param  \Magento\Framework\DB\TransactionFactory                   $transactionFactory
     * @param  \Tonder\Payment\Model\TonderFactory                    $tonderFactory
     * @param  \Tonder\Payment\Model\ResourceModel\Tonder\Collection  $tonderCollection
     * @param  \Magento\Sales\Api\OrderManagementInterface                $orderManagement
     * @param  \Magento\Framework\Filesystem                              $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Tonder\Payment\Helper\Data $helper,
        \Tonder\Payment\Logger\Logger $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Tonder\Payment\Model\TonderFactory $tonderFactory,
        \Tonder\Payment\Model\ResourceModel\Tonder\Collection $tonderCollection,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
        $this->logger = $logger;
        $this->date = $date;
        $this->order = $order;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->invoice = $invoice;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->transactionFactory = $transactionFactory;
        $this->tonderFactory = $tonderFactory;
        $this->tonderCollection = $tonderCollection;
        $this->orderManagement = $orderManagement;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * Processing tonder payment by updating order and creating charge
     *
     * @return  \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $webhookKey = $this->helper->webhookKey();
        $debugMode = true;
        $post = file_get_contents('php://input');
        $headers = apache_request_headers();
        $tonderSign = false;
        foreach ($headers as $header => $value) {
            if ($header == "Tonder-Sign") {
                $tonderSign = $value;
                break;
            }
        }
        $payload = json_encode(json_decode($post));
        if ($debugMode) {
            $this->logger->info("-----Webhook starts-----");
            $this->logger->info("tonder sign => ".$tonderSign);
            $this->logger->info("payload => ".$payload);
        }

        if (!$tonderSign) {
            if ($debugMode) {
                $this->logger->info("-----Webhook ends-----");
            }
            return;
        }
        
        $compareSign = $this->validateTonderSignature($webhookKey, $payload, $tonderSign);

        if ($compareSign) {
            $payload = json_decode($payload);
            list($tonderOrderId, $transactionId, $chargeIdMatched, $orderId) =
                $this->mapOrderWithRequest($payload);

            if ($tonderOrderId && $transactionId && $orderId) {
                if ($payload->event_type=="refund.succeeded" && $chargeIdMatched) {
                    if ($payload->payload->refund_detail->status_details->status=="success") {
                        $refundedAmount = $payload->payload->refund_detail->refunded_amount->amount;
                        if ($debugMode) {
                            $this->logger->info("event_type => refund.succeeded");
                        }
                        $this->createCreditMemo($orderId, $tonderOrderId, $refundedAmount);
                    }
                } elseif ($payload->event_type=="refund.failed" && $chargeIdMatched) {
                    if ($payload->payload->refund_detail->status_details->status=="failure") {
                        if ($debugMode) {
                            $this->logger->info("event_type => refund.failed");
                        }
                        $this->failedRefund($orderId, $tonderOrderId);
                    }
                } elseif ($payload->event_type=="capture.succeeded") {
                    if ($payload->payload->capture_detail->status_details->status=="success") {
                        $capturedAmount = $payload->payload->capture_detail->captured_amount->amount;
                        if ($debugMode) {
                            $this->logger->info("event_type => capture.succeeded");
                        }
                        $this->createInvoiceCapture($orderId, $transactionId, $tonderOrderId, $capturedAmount);
                    }
                } elseif ($payload->event_type=="capture.failed" && $chargeIdMatched) {
                    if ($payload->payload->capture_detail->status_details->status=="failure") {
                        $transactionId = $payload->payload->charge_detail->id;
                        if ($debugMode) {
                            $this->logger->info("event_type => capture.failed");
                        }
                        $this->failedCapture($orderId, $tonderOrderId);
                    }
                } elseif ($payload->event_type=="charge.succeeded") {
                    if ($payload->payload->status_details->status=="success") {
                        $chargeStatus = $this->getValueInAdditionalDetails(
                            $payload->payload->status_details->detail->additional_details,
                            "charge_status"
                        );
                        if ($debugMode) {
                            $this->logger->info("event_type => capture.failed");
                            $this->logger->info("chargeStatus =>". $chargeStatus);
                        }

                        if ($chargeStatus=="captured" || $chargeStatus=="completed") {
                            $this->createInvoice($orderId, $transactionId, $tonderOrderId);
                        }

                    }
                } elseif ($payload->event_type=="charge.pending" && $chargeIdMatched) {
                    if ($payload->payload->status_details->status=="pending") {
                        if ($debugMode) {
                            $this->logger->info("event_type => charge.pending");
                        }
                        $this->pendingCharge($orderId, $tonderOrderId);
                    }
                } elseif ($payload->event_type=="charge.failed" && $chargeIdMatched) {
                    if ($payload->payload->status_details->status=="failure") {
                        if ($debugMode) {
                            $this->logger->info("event_type => charge.failed");
                        }
                        $this->failedCharge($orderId, $tonderOrderId);
                    }
                } elseif ($payload->event_type=="charge.cancelled" && $chargeIdMatched) {
                    if ($payload->payload->status_details->status=="success") {
                        $chargeStatus = $this->getValueInAdditionalDetails(
                            $payload->payload->status_details->detail->additional_details,
                            "charge_status"
                        );
                        if ($debugMode) {
                            $this->logger->info("event_type => capture.cancelled");
                            $this->logger->info("chargeStatus =>". $chargeStatus);
                        }
                        if ($chargeStatus=="failed") {
                            $this->cancelledCharge($orderId, $tonderOrderId);
                        }
                    }
                }
            }
        }
        
        if ($debugMode) {
            $compareSign = $compareSign ? 'true' : 'false';
            $this->logger->info("result => ".$compareSign);
        }

        if ($debugMode) {
            $this->logger->info("-----Webhook ends-----");
        }
    }

    /**
     * Map magento order using tonder System Order Id and charge id from webhook request payload
     *
     * @param  $payload
     * @return array|null
     */
    public function mapOrderWithRequest($payload)
    {
        $tonderOrderId = "";
        $transactionId = "";
        $orderId = "";
        $storedTransactionId = '';

        /**
         * This variable will be used to check if charge id available in magento order and webhook is matched or not
         * if true then we can process all webhooks
         * if false then only process charge success and capture success webhook events only
         */
        $chargeIdMatched = false;

        switch ($payload->event_type) {
            case "refund.succeeded":
            case "refund.failed":
            case "capture.succeeded":
            case "capture.failed":
                $tonderOrderId = $payload->payload->charge_detail->charge->purchase_details->tonder_system_order_id;
                $transactionId = $payload->payload->charge_detail->id;
                break;
            case "charge.succeeded":
            case "charge.pending":
            case "charge.failed":
            case "charge.cancelled":
                $tonderOrderId = $payload->payload->charge->purchase_details->tonder_system_order_id;
                $transactionId = $payload->payload->id;
                break;
        }

        $orderId = $this->getOrderByTonderOrderId($tonderOrderId);

        if ($orderId) {
            $order = $this->order->load($orderId);
            if ($order) {
                $storedTransactionId = $order->getTonderTransactionId();

                if (!empty($transactionId) && $storedTransactionId === $transactionId) {
                    $chargeIdMatched = true;
                } else {
                    $chargeIdMatched = false;
                }

            }
        }
        
        // Log data for debugging
        $this->logger->info("tonder system order id (tonderOrderId) => ".$tonderOrderId);
        $this->logger->info("transactionId stored with magento order (storedTransactionId)=> ".$storedTransactionId);
        $this->logger->info("transactionId from webhook payload (transactionId)=> ".$transactionId);
        $this->logger->info("chargeIdMatched => ".$chargeIdMatched);
        $this->logger->info("magento order id (orderId)=> ".$orderId);
        
        return [$tonderOrderId, $transactionId, $chargeIdMatched, $orderId];
    }

    /**
     * Map additional details object and find value for given key
     *
     * @param array  $additionalDetails
     * @param string $key
     *
     * @return string
     */
    public function getValueInAdditionalDetails($additionalDetails, $key)
    {
        $response = "";
        foreach ($additionalDetails as $val) {
            if ($val->name == $key) {
                $response = $val->data;
            }
        }

        return $response;
    }

    /**
     *
     * @param   RequestInterface    $request
     * @return  InvalidRequestException|null
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     *
     * @param   RequestInterface $request
     * @return  bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Validate Webhook Signature
     *
     * @param    string $key
     * @param    array  $payload
     * @param    string $tonderSignHeader
     * @return   bool
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
     */
    public function validateTonderSignature($key, $payload, $tonderSignHeader)
    {
        [$timestamp, $server_signature] = explode(",", $tonderSignHeader);
        $expiryTime = strtotime('+5 minutes', (int) $timestamp);
        $currentTime = time();
        if ($currentTime <= $expiryTime) {
            $stringToSign = $timestamp . "." . json_encode(json_decode($payload, true), JSON_UNESCAPED_SLASHES);
            $client_signature = strtoupper(hash_hmac('sha256', $stringToSign, $key));
            $signs_are_equal = hash_equals($server_signature, $client_signature);
            return $signs_are_equal;
        } else {
            return false;
        }
    }

    /**
     * @param                                  $orderId
     * @param                                  $tonderOrderId
     * @param                                  $refundedAmount
     * @return                                 void
     * @throws                                 \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function createCreditMemo($orderId, $tonderOrderId, $refundedAmount)
    {
        $directory = $this->filesystem->getDirectoryWrite(
            DirectoryList::TMP
        );

        $unsetFlag = $directory->isDirectory('tonder_refund_'.$orderId);

        if ($unsetFlag) {
            $tmpFileName = $directory->getAbsolutePath(
                'tonder_refund_'.$orderId
            );
            $directory->delete($tmpFileName);
            return;
        }

        $tonder = $this->tonderFactory->create()->load($tonderOrderId, 'tonder_order_id');
        $tonder->setOrderId($orderId);
        $tonder->setTonderOrderId($tonderOrderId);
        $refundStatus = $tonder->getRefundStatus();

        if ($refundStatus!=1 && $refundStatus!=2) {
            $tonder->setRefundStatus(2)->save();

            $order = $this->order->load($orderId);
            $invoices = $order->getInvoiceCollection();
            $invoiceIncrementid = false;
            $invoiceFound = false;
            $orderComment = '';
            foreach ($invoices as $invoice) {
                $invoiceFound = true;
                if ($invoice->getTransactionId()==$order->getTonderTransactionId()) {
                    $invoiceIncrementid = $invoice->getIncrementId();
                }
            }
            if ($invoiceIncrementid) {
                $invoiceObj = $this->invoice->loadByIncrementId($invoiceIncrementid);

                $creditmemo = $this->creditmemoFactory->createByInvoice($invoiceObj);
                if ($invoiceObj->canRefund()) {
                    $adjustmentAmount = $invoiceObj->getAdjustmentAmount();
                    if ($adjustmentAmount) {
                        $adjustmentFee = $creditmemo->getGrandTotal() - ($refundedAmount/100) - $adjustmentAmount;
                    } else {
                        $adjustmentFee = $creditmemo->getGrandTotal() - ($refundedAmount/100);
                    }

                    $orderComment = "The credit memo has been created automatically. Full refund was initiated from Tonder portal.";
                    if ($adjustmentFee>0) {
                        $data = [];
                        $data['shipping_amount'] = 0;
                        foreach ($invoiceObj->getAllItems() as $invoiceItem) {
                            $data['qtys'][$invoiceItem->getOrderItemId()] = 0;
                        }

                        $creditmemo = $this->creditmemoFactory->createByInvoice($invoiceObj, $data);
                        $refundedAmount = $refundedAmount/100;
                        $creditmemo->setAdjustmentPositive($refundedAmount);
                        $creditmemo->setBaseAdjustmentPositive($refundedAmount);
                        $creditmemo->setAdjustment($refundedAmount);
                        $creditmemo->setBaseAdjustment($refundedAmount);
                        $creditmemo->setGrandTotal($refundedAmount);
                        $creditmemo->setBaseGrandTotal($refundedAmount);

                        $orderComment = "The credit memo has been created automatically. Partial refund was initiated from Tonder portal.";
                    } else {
                        $refundedAmount = $refundedAmount/100;
                        $creditmemo->setAdjustmentPositive($refundedAmount);
                        $creditmemo->setBaseAdjustmentPositive($refundedAmount);
                        $creditmemo->setAdjustment($refundedAmount);
                        $creditmemo->setBaseAdjustment($refundedAmount);
                        $creditmemo->setGrandTotal($refundedAmount);
                        $creditmemo->setBaseGrandTotal($refundedAmount);
                    }
                    $this->creditmemoService->refund($creditmemo);
                }
            }
            if (!$invoiceFound) {
                $orderComment = "Full refund was initiated from Tonder portal.";
                $this->orderManagement->cancel($orderId);
            }
            if ($orderComment!='') {
                $order = $this->order->load($orderId);
                $order->addStatusHistoryComment(__($orderComment), false);
                $order->save();
            }
        }
    }

    /**
     *
     * @param  $tonderOrderId
     * @return false
     */
    public function getOrderByTonderOrderId($tonderOrderId)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter('tonder_order_id', ['eq' => $tonderOrderId])
            ->setOrder('created_at', 'desc');
        $orderId = false;
        foreach ($collection as $order) {
            $orderId = $order->getId();
            break;
        }
        return $orderId;
    }

    /**
     *
     * @param  $orderId
     * @param  $transactionId
     * @param  $tonderOrderId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoice($orderId, $transactionId, $tonderOrderId)
    {
        $tonder = $this->tonderFactory->create()->load($tonderOrderId, 'tonder_order_id');
        $invoiceStatus = $tonder->getInvoiceStatus();
        $order = $this->order->load($orderId);
        if (!in_array($invoiceStatus, [1,2,3])) {
            $tonder->setInvoiceStatus(3);
            $tonder->setOrderId($orderId);
            $tonder->save();
            $invoices = $order->getInvoiceCollection();
            $invoiceIncrementid = false;
            foreach ($invoices as $invoice) {
                $invoiceIncrementid = $invoice->getIncrementId();
            }
            if ($invoiceIncrementid==false) {
                if ($order->canInvoice()) {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                    $invoice->register();
                    $invoice->getOrder()->setCustomerNoteNotify(false);
                    $invoice->getOrder()->setIsInProcess(true);
                    $invoice->setTransactionId($transactionId)->setIsTransactionClosed(0);
                    $order->addStatusHistoryComment(__('Automatically INVOICED'), false);
                    $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                    $transactionSave->save();
                    $order->setTonderStatus("success");
                    $order->setTonderTransactionId($transactionId);
                    $order->save();
                }
            }
        }
    }

    /**
     * @param  $orderId
     * @param  $transactionId
     * @param  $tonderOrderId
     * @param  $capturedAmount
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoiceCapture($orderId, $transactionId, $tonderOrderId, $capturedAmount)
    {
        $tonder = $this->tonderFactory->create()->load($tonderOrderId, 'tonder_order_id');
        $invoiceStatus = $tonder->getInvoiceStatus();
        $order = $this->order->load($orderId);
        if (!in_array($invoiceStatus, [1,2,3])) {
            $tonder->setInvoiceStatus(3);
            $tonder->setOrderId($orderId);
            $tonder->save();
            $invoices = $order->getInvoiceCollection();
            $invoiceIncrementid = false;
            foreach ($invoices as $invoice) {
                $invoiceIncrementid = $invoice->getIncrementId();
            }
            if ($invoiceIncrementid==false) {
                
                if ($order->canInvoice()) {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);

                    $invoice->getOrder()->setCustomerNoteNotify(false);
                    $invoice->getOrder()->setIsInProcess(true);
                    $capturedAmount = $capturedAmount/100;

                    if ($invoice->getGrandTotal() > $capturedAmount) {
                        $adjustment = $invoice->getGrandTotal() - $capturedAmount;
                        $invoice->setAdjustmentAmount($adjustment);
                    }


                    $invoice->setGrandTotal($capturedAmount);
                    $invoice->setBaseGrandTotal($capturedAmount);

                    $invoice->register();

                    $invoice->setTransactionId($transactionId)->setIsTransactionClosed(0);
                    $invoice->save();

                    $order->addStatusHistoryComment(__('Automatically INVOICED'), false);
                    $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                    $transactionSave->save();
                    $order->setTonderStatus("success");
                    $order->setTonderTransactionId($transactionId);
                    $order->save();
                }
            }
        }
    }

    /**
     * @param  $orderId
     * @param  $tonderOrderId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function failedCharge($orderId, $tonderOrderId)
    {
        $tonder = $this->tonderFactory->create()->load($tonderOrderId, 'tonder_order_id');
        $chargeStatus = $tonder->getTonderCharge();
        if ($chargeStatus!=1 && $chargeStatus!=null && $orderId!=false) {

            $order = $this->order->load($orderId);
            if ($order->getStatus()!="canceled") {
                $this->orderManagement->cancel($orderId);
                $order->setStatus("canceled");
                $order->setState("canceled");
                $order->addStatusHistoryComment(__('charge.failed event was triggered'), false);
                $order->save();
            }
        }
           return;
    }

    /**
     * @param  $orderId
     * @param  $tonderOrderId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelledCharge($orderId, $tonderOrderId)
    {
        $tonder = $this->tonderFactory->create()->load($tonderOrderId, 'tonder_order_id');
        $chargeStatus = $tonder->getTonderCharge();
        if ($chargeStatus!=1 && $chargeStatus!=null && $orderId!=false) {

            $order = $this->order->load($orderId);
            if ($order->getStatus()!="canceled") {
                $this->orderManagement->cancel($orderId);
                $order->setStatus("canceled");
                $order->setState("canceled");
                $order->addStatusHistoryComment(__('charge.cancelled event was triggered'), false);
                $order->save();
            }
        }
           return;
    }

    /**
     * @param  $orderId
     * @param  $tonderOrderId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function failedRefund($orderId, $tonderOrderId)
    {
        $tonder = $this->tonderFactory->create()->load($tonderOrderId, 'tonder_order_id');
        $chargeStatus = $tonder->getTonderCharge();
        if ($chargeStatus!=1 && $chargeStatus!=null && $orderId!=false) {

            $directory = $this->filesystem->getDirectoryWrite(
                DirectoryList::TMP
            );
            $tmpFileName = $directory->getAbsolutePath(
                'tonder_refund_'.$orderId
            );
            $directory->delete($tmpFileName);

            $order = $this->order->load($orderId);
            $order->addStatusHistoryComment(__('refund.failed event was triggered'), false);
            $order->save();
        }
           return;
    }

    /**
     * @param  $orderId
     * @param  $tonderOrderId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function pendingCharge($orderId, $tonderOrderId)
    {
        $tonder = $this->tonderFactory->create()->load($tonderOrderId, 'tonder_order_id');
        $chargeStatus = $tonder->getTonderCharge();
        if ($chargeStatus!=1 && $chargeStatus!=null) {
            $tonder->setTonderCharge(3);
            $tonder->save();

            $order = $this->order->load($orderId);
            if ($order->getStatus()!="holded" && $order->getStatus()!="pending" && $order->getStatus()!="closed" && $order->getStatus()!="canceled" && $order->getStatus()!="pending_capture" && $order->getStatus()!="payment_review" && $order->getStatus()!="pending_payment") {
                $order->setStatus("pending");
                $order->setState("new");
            }
            $order->addStatusHistoryComment(__('charge.pending event was triggered'), false);
            $order->save();
        }
           return;
    }

    /**
     * @param  $orderId
     * @param  $tonderOrderId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function failedCapture($orderId, $tonderOrderId)
    {
        $tonder = $this->tonderFactory->create()->load($tonderOrderId, 'tonder_order_id');
        $chargeStatus = $tonder->getTonderCharge();
        if ($chargeStatus!=1 && $chargeStatus!=null && $orderId!=false) {
            $order = $this->order->load($orderId);
            $order->addStatusHistoryComment(__('capture.failed event was triggered'), false);
            $order->save();
        }
           return;
    }
}
