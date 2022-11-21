define([
    "jquery",
    "jquery/ui",
    'mage/translate',
    'Magento_Catalog/js/catalog-add-to-cart',
    'Magento_Catalog/product/view/validation',
    'Amasty_Mostviewed/js/ampackcart'
], function ($, ui, transl, mage_addtocart, validation) {
    $.widget('mage.amPackPopupCart', {
        options: {},
        selectors: {
            'forms': '.amrelated-product-info form'
        },

        _create: function (options) {
            var self = this;
            this._createButtonObserve(this.element);
        },

        _createButtonObserve: function (element) {
            var self = this,
                forms = $(self.selectors.forms);
            element.off('click').on('click', function (e) {
                var validator = null,
                    data = [],
                    valid = true;

                e.preventDefault();
                forms.each(function (index, form) {
                    validator = $(form).validation({radioCheckboxClosest: '.nested'});
                    if (!validator.valid()) {
                        valid = false;
                    }
                });

                if (valid) {
                    forms.each(function (index, form) {
                        form = $(form);
                        data[index] = form.serialize();
                    });

                    self.sendAjax(data);
                }
            });
        },

        sendAjax: function (data) {
            var self = this;
            $.ajax({
                url: self.options.url,
                data: {
                    'amrelated_products_popup': data,
                    'form_key': $.mage.cookies.get('form_key')
                },
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    $('body').loader('show');
                },

                success: function (response) {
                    $('[data-amrelated-js="bundle-popup"]').fadeOut();
                    $('[data-amrelated-js="add-to-cart"]').amPackCart('success', response);
                },

                error: function (response) {
                    $('body').loader('hide');
                }
            });
        }
    });

    return $.mage.amPackPopupCart;
});
