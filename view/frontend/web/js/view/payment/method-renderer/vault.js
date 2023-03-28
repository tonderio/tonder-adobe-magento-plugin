/*browser:true*/
define([
    'jquery',
    'underscore',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/payment/additional-validators',
    'ko'
], function ($,
             _,
             VaultComponent,
             globalMessageList,
             fullScreenLoader,
             redirectOnSuccessAction,
             custData,
             urlBuilder,
             storage,
             totals,
             additionalValidators,
             ko
             ) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Tonder_Moneris/payment/vault-form',
            modules: {
                hostedFields: '${ $.parentName }.moneris'
            }
        },
        canUseDSecure: ko.observable(false),

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details.type;
        },

        isUsCountry: function () {
            return window.checkoutConfig.payment[this.getCode()].isUSCountry;
        },

        /**
         * Get payment method data
         * @returns {Object}
         */
        getData: function () {
            var data = {
                'method': this.code,
                'additional_data': {
                    'public_hash': this.publicHash,
                    'is_us': this.isUsCountry()
                }
            };

            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

            return data;
        },

        getConnectionType: function () {
            return this.connectionType;
        },
        getConfig: function (key) {
            if (window.checkoutConfig.payment[this.getCode()][key] !== undefined) {
                return window.checkoutConfig.payment[this.getCode()][key];
            }
            return null;
        },
        hasThreeDSecure: function () {
            if (this.getConfig('isEnable3dS') === 1) {
                return true;
            }
            return false;
        },
        /**
         * check card lookup
         */
        check3DSecure: function () {
            var self = this;
            if(this.hasThreeDSecure()){

                var payload = {
                    use_vault: true,
                    public_hash: self.publicHash
                };
                $.ajax({
                    url: this.getConfig('cardLookupUrl'),
                    dataType: "json",
                    type: 'POST',
                    data: {
                        payload: payload
                    },
                    showLoader: true,
                }).done(function (response) {
                    self.canUseDSecure(response.can_use_3ds)
                    self.placeOrder();
                });
            }else{
                this.placeOrder();
            }

        },
        /**
         * Place order.
         */
        placeOrder: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }

            if (this.validate() &&
                additionalValidators.validate() &&
                this.isPlaceOrderActionAllowed() === true
            ) {
                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .done(
                        function () {
                            if(self.hasThreeDSecure() && self.canUseDSecure()){
                                self.afterPlaceOrder();
                            }else{
                                if (self.redirectAfterPlaceOrder) {
                                    redirectOnSuccessAction.execute();
                                }
                            }
                        }
                    ).always(
                    function () {
                        self.isPlaceOrderActionAllowed(true);
                    }
                );

                return true;
            }

            return false;
        },
        afterPlaceOrder: function () {
            if (event) {
                event.preventDefault();
            }
            fullScreenLoader.startLoader();
            var self = this;
            if (self.validate() && additionalValidators.validate()) {
                var paymentData = window.checkoutConfig.payment.moneris,
                    payload = [],
                    userAgent = navigator.userAgent,
                    quoteData = window.checkoutConfig.quoteData,
                payload = {
                    userAgent: userAgent,
                    paymentData: paymentData,
                    quoteData: quoteData,
                    use_vault: true,
                    additional_data: self.getData()
                };
                var serviceUrl = urlBuilder.createUrl('/moneris/checkout/threedSecure', {});
                storage.post(
                    serviceUrl,
                    JSON.stringify({
                        'payload': payload
                    })
                ).done(
                    function (response) {
                        var obj = JSON.parse(response);
                        if(obj.authentication){
                            if (obj.TransStatus === "C" && obj.ChallengeURL && obj.ChallengeData) {
                                var form = $('<form id="moneris_3d_form" action="' + obj.ChallengeURL + '" method="post">' +
                                    '</form>');
                                $('body').append(form);
                                $('<input>').attr({
                                    type: 'hidden',
                                    name: 'creq',
                                    value: obj.ChallengeData
                                }).appendTo('#moneris_3d_form');

                                form.submit();
                            }else if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        }else{
                            if(obj.redirect_url)
                                window.location.replace(obj.redirect_url);
                        }
                    }
                ).always(
                    function () {
                        fullScreenLoader.stopLoader();
                    }
                );
            }
        },
    });
});
