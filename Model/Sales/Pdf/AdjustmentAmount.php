<?php

namespace Tonder\Payment\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;

class AdjustmentAmount extends DefaultTotal
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @param Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Model\Order\InvoiceRepository $invoiceRepository
     * @param array $data
     */
    public function __construct(
        Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\Order\InvoiceRepository $invoiceRepository,
        array $data = []
    ) {
        $this->request = $request;
        $this->invoiceRepository = $invoiceRepository;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Get totals for adjustment amount
     *
     * @return array|array[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTotalsForDisplay(): array
    {
        $invoiceId = $this->request->getParam('invoice_id');
        if (!$invoiceId) {
            return [];
        }
        $invoice = $this->invoiceRepository->get($invoiceId);
        $adjustmentAmount = $invoice->getAdjustmentAmount();
        if ($adjustmentAmount === null) {
            return [];
        }
        $amountInclTax = $this->getOrder()->formatPriceTxt($adjustmentAmount);
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        return [
            [
                'amount' => $this->getAmountPrefix() . $amountInclTax,
                'label' => __('Adjustment Amount') . ':',
                'font_size' => $fontSize,
            ]
        ];
    }
}
