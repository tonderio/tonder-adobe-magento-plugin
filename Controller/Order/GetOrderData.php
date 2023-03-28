<?php

namespace Tonder\Payment\Controller\Order;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Webapi\Exception;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class GetOrderData extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * GetOrderData constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param Session $checkoutSession
     * @param SessionManager $sessionManager
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        Session $checkoutSession,
        SessionManager $sessionManager
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->sessionManager = $sessionManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     */
    public function execute()
    {
        $controllerResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $orderId = $this->checkoutSession->getData('last_order_id');
        $isUs = $this->getRequest()->getParam('isUs') == "true";
        try {
            $order = $this->orderRepository->get((int) $orderId);
            $shipping = $order->getShippingAddress();
            $billing = $order->getBillingAddress();
            $orderIncrementId = $order->getIncrementId();
            $amount = number_format($order->getBaseTotalDue(), 2, '.', '');
            $info = [];
            if ($shipping) {
                if ($isUs == 'true') {
                    $info += $this->getAddressUS($shipping, 'ship', $order);
                } else {
                    $info += $this->getAddress($shipping, 'ship', $order);
                }
            }
            if ($isUs == 'true') {
                $info += [
                    'amount' => $amount,
                    'order_no' => $orderIncrementId,
                    'client_email' => $order->getCustomerEmail()
                ];
            } else {
                $info += [
                    'charge_total' => $amount,
                    'order_id' => $orderIncrementId,
                    'email' => $order->getCustomerEmail()
                ];
            }
            if ($billing) {
                if ($isUs == 'true') {
                    $info += $this->getAddressUS($billing, 'bill', $order);
                } else {
                    $info += $this->getAddress($billing, 'bill', $order);
                }
            }
            $controllerResult->setData($info);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $controllerResult->setData(['message' => __($e->getMessage())]);
            return $this->getErrorResponse($controllerResult);
        }

        return $controllerResult;
    }

    /**
     * @param ResultInterface $controllerResult
     * @return ResultInterface
     */
    private function getErrorResponse(ResultInterface $controllerResult)
    {
        $controllerResult->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);

        return $controllerResult;
    }

    /**
     * @param $address
     * @param $type
     * @param $order
     * @return array
     */
    private function getAddressUS($address, $type, $order)
    {
        $type = 'od_' . $type . '_';
        return [
            $type . 'firstname'=> $address->getFirstname() ?: 'none',
            $type . 'lastname' => $address->getLastname() ?: 'none',
            $type . 'address' => $address->getStreet() ?: 'none',
            $type . 'company' => $address->getCompany() ?: 'none',
            $type . 'city' => $address->getCity() ?: 'none',
            $type . 'state' => $address->getRegionCode() ?: 'none' ,
            $type . 'phone' => $address->getTelephone() ?: 'none' ,
            $type . 'fax' => $address->getTelephone() ?: 'none' ,
            $type . 'country' => $address->getCountryId() ?: 'none' ,
            $type . 'zipcode' => $address->getPostcode() ?: 'none' ,
            'li_taxes' => sprintf('%.2F', $order->getBaseTaxAmount()),
            'li_shipping' => sprintf('%.2F', $order->getBaseShippingAmount())
        ];
    }

    /**
     * @param $address
     * @param $type
     * @param $order
     * @return array
     */
    private function getAddress($address, $type, $order)
    {
        $type = $type . '_';
        return [
            $type . 'first_name'=> $address->getFirstname() ?: 'none' ,
            $type . 'last_name' => $address->getLastname() ?: 'none' ,
            $type . 'address_one' => $address->getStreet() ?: 'none',
            $type . 'company_name' => $address->getCompany() ?: 'none',
            $type . 'city' => $address->getCity() ?: 'none',
            $type . 'state_or_province' => $address->getRegionCode() ?: 'none',
            $type . 'phone' => $address->getTelephone() ?: 'none' ,
            $type . 'fax' => $address->getTelephone() ?: 'none' ,
            $type . 'country' => $address->getCountryId() ?: 'none' ,
            $type . 'postal_code' => $address->getPostcode() ?: 'none' ,
            'gst' => sprintf('%.2F', $order->getBaseTaxAmount()),
            'shipping_cost' => sprintf('%.2F', $order->getBaseShippingAmount())
        ];
    }
}
