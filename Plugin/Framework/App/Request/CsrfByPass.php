<?php
namespace Tonder\Payment\Plugin\Framework\App\Request;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;

class CsrfByPass
{
    const BY_PASS_URI = [
        '/tonder/payment/complete',
        '/tonder/payment/completeus',
        '/tonder/order/cancel'
    ];

    /**
     * @param \Magento\Framework\App\Request\CsrfValidator $validator
     * @param callable $proceed
     * @param RequestInterface $request
     * @param ActionInterface $action
     * @return bool
     */
    public function aroundValidate(
        \Magento\Framework\App\Request\CsrfValidator $validator,
        callable $proceed,
        RequestInterface $request,
        ActionInterface $action
    ) {
        /** @var State $appState */
        $appState = ObjectManager::getInstance()->get(State::class);
        try {
            $areaCode = $appState->getAreaCode();
        } catch (LocalizedException $exception) {
            $areaCode = null;
        }

        if ($request instanceof HttpRequest
            && in_array(
                $areaCode,
                [Area::AREA_FRONTEND, Area::AREA_ADMINHTML],
                true
            )
        ) {
            if (in_array($request->getPathInfo(), self::BY_PASS_URI)) {
                return true;
            } else {
                $proceed($request, $action);
            }
        }
    }
}
