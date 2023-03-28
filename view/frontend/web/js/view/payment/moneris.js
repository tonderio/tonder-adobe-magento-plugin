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
                type: 'moneris',
                component: 'Tonder_Moneris/js/view/payment/method-renderer/'
                            + window.checkoutConfig.payment.moneris.connectionType
            }
        );

        /**
         * Add view logic here if needed
         */

        return Component.extend({});
    }
);
