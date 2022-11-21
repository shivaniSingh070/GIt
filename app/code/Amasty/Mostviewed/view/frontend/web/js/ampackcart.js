define([
    "jquery",
    "jquery/ui",
    'mage/translate',
    'Magento_Catalog/js/catalog-add-to-cart',
    'Magento_Catalog/product/view/validation'
], function ($, ui, transl, mage_addtocart, validation) {
    $.widget('mage.amPackCart', {
        options: {},
        parent: null,
        selectors: {
            'form': '#product_addtocart_form',
            'parent': '[data-amrelated-js="pack-wrapper"]',
            'mainContainer': '[data-amrelated-js="bundle-popup"]',
            'closePopup': '[data-amrelated-js="close-popup"]',
            'productsWrapper': '[data-amrelated-js="products-wrapper"]'
        },

        _create: function (options) {
            var self = this;
            this._createButtonObserve(this.element);
        },

        _createButtonObserve: function (element) {
            var self = this,
                form = $(self.selectors.form),
                validator = null;
            if (form.length) {
                validator = form.validation({radioCheckboxClosest: '.nested'})
            }

            element.off('click').on('click', function (e) {
                e.preventDefault();
                if (!validator || validator.valid()) {
                    var data = '',
                        parent = $(this).parents(self.selectors.parent),
                        relatedData = parent.find(':input').serialize(),
                        mainProduct = parent.find('[data-amrelated-js="pack-item"].-main');

                    if (form.length) {
                        data = form.serialize();
                    } else {
                        data = 'form_key=' + $.mage.cookies.get('form_key');
                        if (mainProduct.length) {
                            data += '&' +'amrelated_products[]=' + mainProduct.data('product-id');
                        }
                    }

                    data += '&' + relatedData;
                    $.ajax({
                        url: self.options.url,
                        data: data,
                        type: 'post',
                        dataType: 'json',
                        beforeSend: function () {
                            $('body').loader('show');
                        },

                        success: function (response) {
                            self.success(response);
                        },

                        error: function () {
                            $('body').loader('hide');
                            self._scrollToTop();
                        }
                    });
                } else {
                    self._scrollToTop();
                }
            });
        },

        success: function (response) {
            $('body').loader('hide');
            if (response.is_add_to_cart) {
                this._scrollToTop();
                if ($('body').hasClass('checkout-cart-index')) {
                    window.location.reload();
                }
            } else {
                this.showProductPopup(response);
            }
        },

        _scrollToTop: function () {
            $('html,body').animate({
                scrollTop: 0
            }, 'slow');
        },

        showProductPopup: function (products) {
            var self = this,
                oldPopup = $(this.selectors.mainContainer),
                popup = $(products.html);

            if (oldPopup.length > 0) {
                oldPopup.remove();
            }

            popup.find(self.selectors.closePopup).on('click', function () {
                popup.fadeOut();
            });

            popup.on('click', function (event) {
                if (!($(event.target).hasClass('amrelated-bundle-popup')
                        || $(event.target).parents().hasClass('amrelated-bundle-popup'))
                ) {
                    popup.fadeOut();
                }
            });

            popup.hide().appendTo($('body')).fadeIn();

            //fix magento swatches scroll issue
            $(self.selectors.productsWrapper).on('scroll', function () {
                $('.swatch-option-tooltip').hide();
            });

            $(window).on('scroll', function () {
                if (popup.css('display') != 'none') {
                    $('.swatch-option-tooltip').hide();
                }
            });
            $('data-amrelated-js="bundle-popup"').trigger('contentUpdated');
        }
    });

    return $.mage.amPackCart;
});
