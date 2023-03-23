define(
    [
        'underscore',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/quote',
        'mage/translate',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (_, $, Component, quote, $t, fullScreenLoader) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Tonder_Payment/payment/tonder',
                active: false,
                paymentMethodNonce: null,
                lastBillingAddress: null,
                // validatorManager: validatorManager,
                code: 'tonder',
                isProcessing: false,

                /**
                 * Additional payment data
                 *
                 * {Object}
                 */
                additionalData: {},

                /**
                 * Braintree client configuration
                 *
                 * {Object}
                 */
                clientConfig: {
                    onReady: function (context) {
                        context.setupHostedFields();
                    },

                    /**
                     * Triggers on payment nonce receive
                     * @param {Object} response
                     */
                    onPaymentMethodReceived: function (response) {
                        this.handleNonce(response);
                        this.isProcessing = false;
                    },

                    /**
                     * Allow a new nonce to be generated
                     */
                    onPaymentMethodError: function() {
                        this.isProcessing = false;
                    },

                    /**
                     * Device data initialization
                     * @param {String} deviceData
                     */
                    onDeviceDataRecieved: function (deviceData) {
                        this.additionalData['device_data'] = deviceData;
                    },

                    /**
                     * After Braintree instance initialization
                     */
                    onInstanceReady: function () {},

                    /**
                     * Triggers on any Braintree error
                     * @param {Object} response
                     */
                    onError: function (response) {
                        this.isProcessing = false;
                        throw response.message;
                    },

                    /**
                     * Triggers when customer click "Cancel"
                     */
                    onCancelled: function () {
                        this.paymentMethodNonce = null;
                        this.isProcessing = false;
                    }
                },
                imports: {
                    onActiveChange: 'active'
                }
            },

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                // validator.setConfig(window.checkoutConfig.payment[this.getCode()]);
                this._super()
                    .observe(['active']);
                // this.validatorManager.initialize();
                this.initClientConfig();

                return this;
            },

            /**
             * Get payment name
             *
             * @returns {String}
             */
            getCode: function () {
                return this.code;
            },

            /**
             * Check if payment is active
             *
             * @returns {Boolean}
             */
            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },

            /**
             * Triggers when payment method change
             * @param {Boolean} isActive
             */
            onActiveChange: function (isActive) {
                if (!isActive) {
                    return;
                }

                this.initBraintree();
            },

            /**
             * Init config
             */
            initClientConfig: function () {
                _.each(this.clientConfig, function (fn, name) {
                    if (typeof fn === 'function') {
                        this.clientConfig[name] = fn.bind(this);
                    }
                }, this);
            },

            /**
             * Init Braintree configuration
             */
            initBraintree: function () {

                // fullScreenLoader.startLoader();

            },

            /**
             * Get full selector name
             *
             * @param {String} field
             * @returns {String}
             */
            getSelector: function (field) {
                return '#' + this.getCode() + '_' + field;
            },

            /**
             * Get list of available CC types
             *
             * @returns {Object}
             */
            getCcAvailableTypes: function () {
                return window.checkoutConfig.payment.ccform.availableTypes[this.getCode()];
            },

            /**
             * @returns {String}
             */
            getEnvironment: function () {
                return window.checkoutConfig.payment[this.getCode()].environment;
            },

            /**
             * Get data
             *
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce
                    }
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

                return data;
            },

            /**
             * Set payment nonce
             * @param {String} paymentMethodNonce
             */
            setPaymentMethodNonce: function (paymentMethodNonce) {
                this.paymentMethodNonce = paymentMethodNonce;
            },

            /**
             * Prepare data to place order
             * @param {Object} data
             */
            handleNonce: function (data) {
                var self = this;

                this.setPaymentMethodNonce(data.nonce);

                // place order on success validation
                // self.validatorManager.validate(self, function () {
                //     return self.placeOrder('parent');
                // }, function() {
                //     self.isProcessing = false;
                //     self.paymentMethodNonce = null;
                // });
            },

            /**
             * Action to place order
             * @param {String} key
             */
            placeOrder: function (key) {
                if (key) {
                    return this._super();
                }

                if (this.isProcessing) {
                    return false;
                } else {
                    this.isProcessing = true;
                }

                return false;
            },

            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },
        });
    }
);
