<?php

namespace Tonder\Payment\Plugin;

use Signifyd\Connect\Observer\PreAuth;
use Tonder\Payment\Logger\Logger;

class SignifydPreAuthPlugin
{

    /**
     * @var \Tonder\Payment\Helper\Data
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Tonder\Payment\Helper\Data $helper
     */
    public function __construct(
        \Tonder\Payment\Helper\Data $helper,
        Logger $logger
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Summary of afterGetStopCheckoutProcess
     * @param PreAuth $subject
     * @param callable $proceed
     * @param mixed $caseResponse
     * @param mixed $caseAction
     * @return mixed
     */
    public function afterGetStopCheckoutProcess(PreAuth $subject, $result)
    {
        $processSignifydRejectedOrder = $this->helper->getProcessSignifydRejectedOrder();

        $this->logger->info("afterGetStopCheckoutProcess -----");
        $this->logger->info("value from signifyd function getStopCheckoutProcess - caseAction  -  " . ($result ? 'REJECT' : 'ACCEPTED' ));
        $this->logger->info("value stored in admin for processSignifydRejectedOrder - " . ($processSignifydRejectedOrder ? 'Yes' : 'No' ));
        if ($processSignifydRejectedOrder) {
            $this->logger->info("Order Rejected by Signifyd should be processed by Tonder");
        } else {
            $this->logger->info("Order Rejected by Signifyd so should not be processed further by Tonder");
        }

        // We negate the value in order to pass or fail the condition
        // which throw rejected exception in signifyd, so if the value saved in admin is
        // - Yes / true, then we pass false which fails the condition to throw exception and we can proceed with charge
        // - No / false, then we pass true and then exception will be thrown and checkout process will stop there only
        return !$processSignifydRejectedOrder;
    }
}
