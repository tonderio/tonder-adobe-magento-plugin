<?php

namespace Tonder\Payment\Block\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context ;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Request\Http;

class View extends Template
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param Http $request
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Http $request,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->request = $request;
    }

    /**
     * Get Order by order id
     *
     * @param int $orderId
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderById($orderId)
    {
        try {
            $orderId = $this->request->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            return false;
        }
        return  $order;
    }

    /**
     * Get Order detail
     *
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderDetail()
    {
        try {
            $orderId = $this->request->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            return false;
        }
        return  $order;
    }

    /**
     * Get Order detail
     *
     * @param int $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderDetailById($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        return $order;
    }
}
