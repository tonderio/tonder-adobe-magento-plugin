<?php

declare(strict_types=1);

namespace Tonder\Payment\Plugin\Model\Service;

use Magento\Sales\Model\Order;

class InvoiceService
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Set adjustment amount for invoice
     *
     * @param \Magento\Sales\Model\Service\InvoiceService $subject
     * @param mixed $result
     * @param Order $order
     * @param array $orderItemsQtyToInvoice
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareInvoice(
        \Magento\Sales\Model\Service\InvoiceService $subject,
        $result,
        Order $order,
        array $orderItemsQtyToInvoice = []
    ) {
        $invoiceData = $this->request->getParam('invoice', []);
        if (isset($invoiceData['adjustment_amount'])) {
            $adjustment = $invoiceData['adjustment_amount'];
            $result->setAdjustmentAmount($adjustment);
            $result->setGrandTotal($result->getGrandTotal() + $adjustment);
        }

        return $result;
    }
}
