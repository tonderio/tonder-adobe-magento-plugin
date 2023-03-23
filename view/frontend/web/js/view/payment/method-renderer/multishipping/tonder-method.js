define([
    'jquery',
    'Magenest_Moneris/js/view/payment/method-renderer/direct',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/set-payment-information-extended',
    'Magento_Payment/js/model/credit-card-validation/validator'
], function (
    $,
    Component,
    additionalValidators,
    messageList,
    $t,
    fullScreenLoader,
    setPaymentInformationExtended,
    validatorManager
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magenest_Moneris/payment/multishipping/moneris-direct-form.html',
            submitButtonSelector: '#payment-continue span',
            monerisButtonSelector: '[id="parent-payment-continue"]',
            reviewButtonHtml: '',
            imports: {
                onActiveChange: 'active'
            }
        },

        initObservable: function () {
            this.reviewButtonHtml = $(this.monerisButtonSelector).html();

            return this._super();
        },

        /**
         * @override
         */
        beforePlaceOrder: function (data) {
            this._super(data);

            this.updateSubmitButton(true);
        },

        /**
         * @override
         */
        getShippingAddress: function () {
            return {};
        },

        /**
         * @override
         */
        getData: function () {
            var data = this._super();

            data['additional_data']['is_active_payment_token_enabler'] = true;

            return data;
        },

        /**
         * @override
         */
        isActiveVault: function () {
            return true;
        },

        /**
         * Skipping order review step on checkout with multiple addresses is not allowed.
         *
         * @returns {Boolean}
         */
        isSkipOrderReview: function () {
            return false;
        },
        /**
         * check multiShipping for 3DS
         * @returns {boolean}
         */
        isMultiShipping: function () {
            return true;
        },
        /**
         * Updates submit button on multi-addresses checkout billing form.
         *
         * @param {Boolean} isActive
         */
        updateSubmitButton: function (isActive) {
            if (!isActive) {
                $(this.paypalButtonSelector).html(this.reviewButtonHtml);
            }
        },

        /**
         * @override
         */
        placeOrder: function () {
            fullScreenLoader.startLoader();
            $.when(
                setPaymentInformationExtended(
                    this.messageContainer,
                    this.getData(),
                    true
                )
            ).done(this.done.bind(this))
                .fail(this.fail.bind(this));
        },

        /**
         * {Function}
         */
        fail: function () {
            fullScreenLoader.stopLoader();

            return this;
        },

        /**
         * {Function}
         */
        done: function () {
            fullScreenLoader.stopLoader();
            $('#multishipping-billing-form').submit();

            return this;
        }
    });
});
