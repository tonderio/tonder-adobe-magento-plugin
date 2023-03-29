<?php
namespace Tonder\Payment\Controller\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class PostBack extends AbstractPostBack
{
    const RESPONSE_CODE = 'ResponseCode';

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $response = $this->getRequest()->getParams();
            if (isset($response['order_id'])) {
                $order = $this->getOrder($response['order_id']);
                if (!$order->getCustomerIsGuest()) {
                    $this->_getCustomerSession()->setIsLoggedIn(true);
                    $this->_getCustomerSession()->setCustomerId($order->getCustomerId());
                }
                /** "last successful quote" */
                $this->_getCheckoutSession()->setLastQuoteId($order->getQuoteId())->setLastSuccessQuoteId($order->getQuoteId());

                $this->_getCheckoutSession()->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());
                if (!isset($response['cres']) || empty($response['cres'])) {
                    throw new LocalizedException(__("We can\'t find cres"));
                }
                $store_id = $this->config->getValue('store_id');
                $api_token= $this->config->getValue('api_token');
                $txnArray= [
                    'type' => 'cavv_lookup',
                    'cres' => $response['cres'],
                    'order_id' => $order->getRealOrderId()
                ];
                $arguments['payment'] = $this->paymentDataObjectFactory->create($order->getPayment());
                $arguments['response'] = $cavvResponse;
                $arguments['amount'] = sprintf('%.2F', $order->getTotalDue());
                $this->commandPool->get('three_d_secure')->execute($arguments);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            if ($order->getId()) {
                $this->addCommentHistoryOrder($order, $e->getMessage());
            }
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('moneris/order/cancel', ['order_id' => $order->getId()]);
            return $resultRedirect;
        }

        $resultRedirect->setPath('checkout/onepage/success');
        return $resultRedirect;
    }

    /**
     * @param $response
     * @return bool
     */
    public function validateCavvResponse($response)
    {
        return empty($response['Cavv']) || ($response[self::RESPONSE_CODE] != 'null' && (int)$response[self::RESPONSE_CODE] > 50);
    }

    /**
     * @param $order
     * @param $comment
     */
    public function addCommentHistoryOrder($order, $comment)
    {
        $comment = $order->addCommentToStatusHistory($comment);
        try {
            $this->orderStatusRepository->save($comment);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
