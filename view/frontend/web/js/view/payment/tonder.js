define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'tonder',
                component: 'Tonder_Payment/js/view/payment/method-renderer/tonder-method'
            }
        );
        return Component.extend({});
    }
);