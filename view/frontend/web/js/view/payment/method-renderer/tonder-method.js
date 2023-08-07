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
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data'
    ],
    function ($,
              Component,
              additionalValidators,
              ko,
              alert,
              address,
              validator,
              fullScreenLoader,
              urlBuilder,
              storage,
              redirectOnSuccessAction,
              cardNumberValidator,
              creditCardData
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Tonder_Payment/payment/tonder',
                active: false,
                scriptLoaded: false,
                saveKeyData: false,
                creditCardHolderName: null,
                imports: {
                    onActiveChange: 'active'
                },
                code: 'tonder',
            },
            placeOrderHandler: null,
            validateHandler: null,
            canUseDSecure: ko.observable(false),

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe(
                        [
                            'active',
                            'saveKeyData',
                            'creditCardHolderName'
                        ]);
                return this;
            },

            disableArrowKeys: function (event, length = false) {
                if (length && parseInt(event.key) >= 0 && parseInt(event.key) <= 9) {
                    if ($(event.currentTarget).val().length >= length) {
                        event.preventDefault();
                    }
                }
                if (event.which === 38 || event.which === 40) {
                    event.preventDefault();
                } else {
                    return true;
                }
            },
            disableArrowKeysCC: function (event, length = false) {
                if (length && parseInt(event.key) >= 0 && parseInt(event.key) <= 4) {
                    if ($(event.currentTarget).val().length >= length) {
                        event.preventDefault();
                    }
                }
                if (event.which === 38 || event.which === 40) {
                    event.preventDefault();
                } else {
                    return true;
                }
            },

            validateInput: function (e) {
                $(e).valid('isValid');
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
                return this.code;
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
            getData: function () {
                var data = this._super();
                data['additional_data']['cc_card_holder_name'] = this.creditCardHolderName();
                return data;
            },

            saveKey: function () {
                var self = this;

                $.ajax({
                    url: self.getKeyDataUrl(),
                    data: {
                        'isUs': self.isUsCountry(),
                        'form_key': window.checkoutConfig.formKey,
                        'card_data': this.getData(),
                        'address': {
                            'street': address().currentBillingAddress()['street'],
                            'post_code': address().currentBillingAddress()['postcode']
                        }
                    },
                    type: 'POST',
                    showLoader: true
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
                var availableTypesValues = this.getCcAvailableTypesValues();
                var valid = false;
                var value = $('#tonder_cc_number').val();
                value = value.slice(0, 16);
                $('#tonder_cc_number').val(value);
                var result = cardNumberValidator(value);

                if (!result.isPotentiallyValid && !result.isValid) {
                    this.selectedCardType(null);
                    return false;
                }

                if (result.card !== null) {
                    this.selectedCardType(result.card.type);
                    creditCardData.creditCard = result.card;
                }

                if (result.isValid) {
                    creditCardData.creditCardNumber = value;
                    this.creditCardType(result.card.type);
                }
                var type = this.selectedCardType();

                for (var i = 0; i < availableTypesValues.length; i++) {
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
                    if (!this.saveKeyData()) {
                        this.placeOrder();
                    } else {
                        return this.saveKey() ? true : null;
                    }
                } else {
                    alert({
                        title: 'ERROR',
                        content: 'Please provide credit card information first!',
                        clickableOverlay: true,
                        actions: {
                            always: function () {
                            }
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
                                if (self.redirectAfterPlaceOrder) {
                                    redirectOnSuccessAction.execute();
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

            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },

            /**
             * Get payment icons
             * @param {String} type
             * @returns {Boolean}
             */
            getIcons: function (type) {
                return window.checkoutConfig.payment.tonder_ccform.icons.hasOwnProperty(type) ?
                    window.checkoutConfig.payment.tonder_ccform.icons[type]
                    : false;
            },

            /**
             * Get payment field label
             * @param {String} field
             * @returns {Boolean}
             */
            getFormFieldLabel: function (field) {
                return window.checkoutConfig.payment.tonder_ccform.form_configuration.hasOwnProperty(field) ?
                    window.checkoutConfig.payment.tonder_ccform.form_configuration[field]
                    : false;
            },

            /**
             * Get month label
             * @param {String} field
             * @returns {Boolean}
             */
            getMonthLabel: function (field) {
                return window.checkoutConfig.payment.tonder_ccform.form_configuration.month_labels.hasOwnProperty(field) ?
                    window.checkoutConfig.payment.tonder_ccform.form_configuration.month_labels[field]
                    : false;
            },

            /**
             * Get list of available month values
             * @returns {Object}
             */
            getCcMonthsValues: function () {
                var self = this;
                return _.map(self.getCcMonths(), function (value, key) {
                    value = self.getMonthLabel(Number(key) - 1) || value;

                    return {
                        'value': key,
                        'month': value
                    };
                });
            },
        });
    }
);
