<?php

namespace Tonder\Payment\Block\Adminhtml\Sales\Order;

use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Backend\Block\Template;

class Tonder extends Template
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        Http $request,
        OrderRepositoryInterface $orderRepository,
        Template\Context $context,
        array $data = []
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get order detail
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderDetail()
    {
        $orderId = $this->request->getParam('order_id');
        $order = $this->orderRepository->get($orderId);

        return $order;
    }
}
