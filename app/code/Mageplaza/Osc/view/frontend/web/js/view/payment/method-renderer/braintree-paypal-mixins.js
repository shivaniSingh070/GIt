/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Mageplaza_Osc/js/action/set-checkout-information',
    'Mageplaza_Osc/js/model/braintree-paypal',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/quote',
    'underscore',
    'uiRegistry',
    'braintreeCheckoutPayPalAdapter',
    'mage/translate'
], function ($,
             setCheckoutInformationAction,
             braintreePaypalModel,
             additionalValidators,
             quote,
             _,
             registry,
             Braintree,
             $t) {
    'use strict';
    return function (BraintreePaypalComponent) {
        return BraintreePaypalComponent.extend({
            defaults: {
                template: 'Mageplaza_Osc/payment/braintree_paypal_map',

                clientConfig: {
                    buttonPayPalId: 'osc_braintree_paypal_placeholder',
                    buttonId: 'osc_braintree_paypal_placeholder',
                }
            },

            /**
             * Set list of observable attributes
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                this._super();
                // For each component initialization need update property
                this.isReviewRequired = braintreePaypalModel.isReviewRequired;
                this.customerEmail = braintreePaypalModel.customerEmail;
                this.active = braintreePaypalModel.active;

                return this;
            },

            /**
             * Get shipping address
             * @returns {Object}
             */
            getShippingAddress: function () {
                var address = quote.shippingAddress();

                if (!address) {
                    address = {};
                }
                if (!address.street) {
                    address.street = ['', ''];
                }
                if (address.postcode === null) {
                    return {};
                }

                return  this._super();
            },

            loadPayPalButton: function (paypalCheckoutInstance, funding) {
                var paypalPayment = Braintree.config.paypal,
                    onPaymentMethodReceived = Braintree.config.onPaymentMethodReceived,
                    style = {
                        color: Braintree.getColor(),
                        shape: Braintree.getShape(),
                        layout: Braintree.getLayout(),
                        size: Braintree.getSize()
                    };

                if (Braintree.getBranding()) {
                    style.branding = Braintree.getBranding();
                }
                if (Braintree.getFundingIcons()) {
                    style.fundingicons = Braintree.getFundingIcons();
                }

                if (funding === 'credit') {
                    style.layout = "horizontal";
                    style.color = "darkblue";
                    Braintree.config.buttonId = this.clientConfig.buttonCreditId;
                } else if (funding === 'paylater') {
                    style.layout = "horizontal";
                    style.color = "white";
                    Braintree.config.buttonId = this.clientConfig.buttonPaylaterId;
                } else {
                    Braintree.config.buttonId = this.clientConfig.buttonPayPalId;
                }
                // Render
                Braintree.config.paypalInstance = paypalCheckoutInstance;
                var events = Braintree.events;
                $('#' + Braintree.config.buttonId).html('');

                var button = paypal.Buttons({
                    fundingSource: funding,
                    env: Braintree.getEnvironment(),
                    style: style,
                    commit: true,
                    locale: Braintree.config.paypal.locale,

                    createOrder: function () {
                        return paypalCheckoutInstance.createPayment(paypalPayment);
                    },

                    onCancel: function (data) {
                        console.log('checkout.js payment cancelled', JSON.stringify(data, 0, 2));

                        if (typeof events.onCancel === 'function') {
                            events.onCancel();
                        }
                    },

                    onError: function (err) {
                        Braintree.showError($t("PayPal Checkout could not be initialized. Please contact the store owner."));
                        Braintree.config.paypalInstance = null;
                        console.error('Paypal checkout.js error', err);

                        if (typeof events.onError === 'function') {
                            events.onError(err);
                        }
                    }.bind(this),

                    onClick: function(data) {
                        if (additionalValidators.validate()) {
                            setCheckoutInformationAction();

                            if (typeof events.onClick === 'function') {
                                events.onClick(data);
                            }
                        }
                    },

                    onApprove: function (data, actions) {
                        return paypalCheckoutInstance.tokenizePayment(data)
                        .then(function (payload) {
                            onPaymentMethodReceived(payload);
                        });
                    }

                });
                if (button.isEligible()) {
                    button.render('#' + Braintree.config.buttonId).then(function () {
                        Braintree.enableButton();
                        if (typeof Braintree.config.onPaymentMethodError === 'function') {
                            Braintree.config.onPaymentMethodError();
                        }
                    }.bind(this)).then(function (data) {
                        if (typeof events.onRender === 'function') {
                            events.onRender(data);
                        }
                    });
                }
            },

            // Compatible with PayPal Through Braintree on M231
            reInitPayPal: function () {
                var placeOrder = registry.get('checkout.sidebar.place-order-information-right.place-order-button');

                if (!placeOrder.isPaypalThroughBraintree) {
                    this._super();
                }
            }
        });
    };
});
