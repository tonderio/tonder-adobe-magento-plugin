define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/payment/additional-validators',
        'ko',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/view/billing-address',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/action/redirect-on-success',
    ],
    function ($,
              ccFormComponent,
              additionalValidators,
              ko,
              alert,
              address,
              validator,
              fullScreenLoader,
              urlBuilder,
              storage,
              redirectOnSuccessAction
              ) {
        'use strict';

        return ccFormComponent.extend({
            defaults: {
                template: 'Tonder_Moneris/payment/moneris-direct-form',
                active: false,
                scriptLoaded: false,
                saveKeyData: false,
                creditCardHolderName: null,
                imports: {
                    onActiveChange: 'active'
                }
            },
            placeOrderHandler: null,
            validateHandler: null,
            canUseDSecure: ko.observable(false),

            initObservable: function () {
                this._super()
                    .observe('active');
                this._super()
                    .observe('saveKeyData');
                this._super()
                    .observe('creditCardHolderName');
                return this;
            },

            disableArrowKeys: function (event) {
                if ( event.which === 38 || event.which === 40 ) {
                    event.preventDefault();
                } else {
                    return true;
                }
            },

            validateInput: function (e) {
                var ele = $(e);

                if (ele.attr('value') !== "") {
                    ele.valid('isValid');
                }
            },

            context: function () {
                return this;
            },

            getConfig: function (key) {
                if (window.checkoutConfig.payment[this.getCode()][key] !== undefined) {
                    return window.checkoutConfig.payment[this.getCode()][key];
                }
                return null;
            },

            hasVerification: function () {
                if (this.getConfig('cvd') === 1) {
                    return true;
                }
                return false;
            },
            hasThreeDSecure: function () {
                if (this.getConfig('isEnable3dS') === 1) {
                    return true;
                }
                return false;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },


            getCode: function () {
                return 'moneris';
            },

            getKeyDataUrl: function () {
                return window.checkoutConfig.payment[this.getCode()].getKeyData;
            },

            isVisible: function () {
                if (window.checkoutConfig.payment[this.getCode()].isVaultEnabled === 1) {
                    return window.checkoutConfig.payment[this.getCode()].isLoggedIn && 1;
                }
                return false;
            },

            isProvided: function () {
                if(this.hasThreeDSecure() && !this.creditCardHolderName() && !this.isMultiShipping()){
                    return false;
                }
                if (this.creditCardNumber() && this.creditCardExpMonth()
                    && this.creditCardExpYear() && this.selectedCardType()) {
                    return true;
                }
                return false;
            },

            isUsCountry: function () {
                return window.checkoutConfig.payment[this.getCode()].isUSCountry;
            },

            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },
            isMultiShipping: function () {
                return false;
            },

            saveKey: function () {
                var self = this;

                $.ajax({
                    url : self.getKeyDataUrl(),
                    data : {
                        'isUs' : self.isUsCountry(),
                        'form_key' : window.checkoutConfig.formKey,
                        'card_data' : this.getData(),
                        'address': {
                            'street': address().currentBillingAddress()['street'],
                            'post_code': address().currentBillingAddress()['postcode']
                        }
                    },
                    type : 'POST',
                    showLoader : true
                }).done(
                    function (response) {
                        this.placeOrder();
                        return response;
                    }.bind(this)

                ).fail(
                    function () {
                        return false;
                    }
                );
            },

            creditCardValidate: function () {
                var type = this.selectedCardType();
                var availableTypesValues = this.getCcAvailableTypesValues();
                var valid = false;
                var i = 0;

                for (i = 0; i < availableTypesValues.length; i++) {
                    if (type === availableTypesValues[i]["value"]) {
                        valid = true;
                        break;
                    }
                }
                if (!valid) {
                    this.selectedCardType(null);
                }
            },

            checkOption: function () {
                var self = this;
                if (!$('#co-transparent-form').valid('isValid')) {
                    return null;
                }
                if (this.isProvided()) {
                    if(this.hasThreeDSecure() && !this.isMultiShipping()){
                        var cardData = {
                            cardHolderName: this.creditCardHolderName(),
                            accountNumber: this.creditCardNumber(),
                            expMonth: this.creditCardExpMonth(),
                            expYear: this.creditCardExpYear()
                        };
                        var payload = {
                            cardData: cardData
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
                            if (!self.saveKeyData()) {
                                self.placeOrder();
                            } else {
                                return self.saveKey() ? true : null;
                            }
                        });
                    }else {
                        if (!this.saveKeyData()) {
                            this.placeOrder();
                        } else {
                            return this.saveKey() ? true : null;
                        }
                    }
                } else {
                    alert({
                        title: 'ERROR',
                        content: 'Please provide credit card information first!',
                        clickableOverlay: true,
                        actions: {
                            always: function (){}
                        }
                    });
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
                        cardData = [],
                        payload = [],
                        userAgent = navigator.userAgent,
                        quoteData = window.checkoutConfig.quoteData,
                    cardData = {
                        cardHolderName: this.creditCardHolderName(),
                        accountNumber: this.creditCardNumber(),
                        expMonth: this.creditCardExpMonth(),
                        expYear: this.creditCardExpYear()
                    };
                    payload = {
                        cardData: cardData,
                        userAgent: userAgent,
                        paymentData: paymentData,
                        quoteData: quoteData,
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
    }
);
