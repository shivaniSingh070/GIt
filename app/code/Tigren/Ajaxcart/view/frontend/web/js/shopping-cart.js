/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'jquery/ui'
], function ($, getTotalsAction, customerData, alert, $t) {
    'use strict';

    $.widget('tigren.shoppingCart1', {
        /** @inheritdoc */
        _create: function () {
            var items, i, eventFired = 0;
            var self = this;
            $(self.options.emptyCartButton).on('click', $.proxy(function () {
                $(self.options.emptyCartButton).attr('name', 'update_cart_action_temp');
                $(self.options.updateCartActionContainer)
                    .attr('name', 'update_cart_action').attr('value', 'empty_cart');
            }, self));
            items = $.find('[data-role="cart-item-qty"]');

            for (i = 0; i < items.length; i++) {
                $(items[i]).on('keypress', $.proxy(function (event) { //eslint-disable-line no-loop-func
                    var keyCode = event.keyCode ? event.keyCode : event.which;

                    if (keyCode == 13) { //eslint-disable-line eqeqeq
                        $(self.options.emptyCartButton).attr('name', 'update_cart_action_temp');
                        $(self.options.updateCartActionContainer)
                            .attr('name', 'update_cart_action').attr('value', 'update_qty');

                    }
                }, self));
            }
            $(self.options.continueShoppingButton).on('click', $.proxy(function () {
                location.href = self.options.continueShoppingUrl;
            }, self));

            $(window).on("load", function () {
                $('.quantity-controls.quantity-plus').attr('disabled', true);
                $('.quantity-controls.quantity-minus').attr('disabled', true);
            });

            $('.quantity-controls.quantity-plus').removeAttr('disabled');
            $('.quantity-controls.quantity-minus').removeAttr('disabled');


            $('.quantity-controls.quantity-minus').on("click", function (e) {
                e.preventDefault();
                var qty = parseInt($(this).parent().find('input[name$="[qty]"]').val()),
                    form = $('form#form-validate');
                if (!qty || qty == 0) qty = 1;
                if (qty > 1) {
                    $(this).parent().find('input[name$="[qty]"]').val(qty - 1);
                }
                //ajax submit
                self._ajaxUpdateHiddenField(form)
                self._ajaxCartUpdate(form);
            });

            $('.quantity-controls.quantity-plus').on("click", function (e) {
                e.preventDefault();
                var qty = parseInt($(this).parent().find('input[name$="[qty]"]').val()),
                    form = $('form#form-validate');
                if (!qty || qty == 0) qty = 1;
                $(this).parent().find('input[name$="[qty]"]').val(qty + 1);
                //ajax submit
                self._ajaxUpdateHiddenField(form)
                self._ajaxCartUpdate(form);
            });

            $('input[name$="[qty]"]').on("change", function () {
                if(eventFired > 0){
                    return;
                }
                var form = $('form#form-validate'),
                    qtyOrdered = $(this).attr('data-cart-ordered'),
                    productName = $(this).closest('.cart.item').find('.product-item-name a').html(),
                    qty = $(this).parent().find('input[name$="[qty]"]');

                if(self._checkStockState(this)){
                    self._showPopupErrorQty(productName);
                    $(this).val(qtyOrdered);
                    return;
                }
                self._ajaxUpdateHiddenField(form);
                self._ajaxCartUpdate(form);
            });

            $('input[name$="[qty]"]').on("keyup", function (e) {
                var key = e.keyCode ? e.keyCode : e.which,
                    qtyOrdered = $(this).attr('data-cart-ordered'),
                    productName = $(this).closest('.cart.item').find('.product-item-name a').html(),
                    form = $('form#form-validate');
                eventFired = 0;
                if (49 <= key && key <= 57 || 96 <= key && key <= 105) {
                    if(self._checkStockState(this)){
                        self._showPopupErrorQty(productName);
                        $(this).val(qtyOrdered);
                        return;
                    }
                    self._ajaxUpdateHiddenField(form);
                    self._ajaxCartUpdate(form);
                    eventFired += 1;
                }
            });

        },


        _ajaxCartUpdate(form) {
            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                showLoader: true,
                success: function (res) {
                    if (res.error === true) {
                        alert({
                            title: 'Hello',
                            content: res.message,
                            actions: {
                                always: function () {
                                }
                            }
                        });
                        return;
                    } else {
                        var parsedResponse = $.parseHTML(res);
                        var result = $(parsedResponse).find("#form-validate");
                        $("#form-validate").replaceWith(result);

                        customerData.reload(['cart'], true);

                        var deferred = $.Deferred();
                        getTotalsAction([], deferred);
                    }
                },
                error: function (xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                }

            });
        },

        _ajaxUpdateHiddenField(form){
            var inputSelector = $('#additional_input_data'),
                html = inputSelector.html();
            if (form.length > 0) {
                inputSelector.remove();
                form.append(html);
            }
        },

        _checkStockState(qtySelector){
            var stockState = parseInt($(qtySelector).attr('data-cart-max')),
                currentQty = parseInt($(qtySelector).val());
            return stockState < currentQty;
        },

        _showPopupErrorQty(productName){
            alert({
                content: $t('We don\'t have as many %1 as you requested').replace('%1', productName),
                actions: {
                    always: function () {
                    }
                }
            });
        }

    });

    return $.tigren.shoppingCart1;
});