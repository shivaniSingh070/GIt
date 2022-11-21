define([
    "jquery",
    "jquery/ui",
    'Magento_Catalog/js/price-utils',
    'Magento_Catalog/js/price-box',
], function ($, ui, utils) {
    $.widget('mage.amPack', {
        options: {},
        excluded: [],
        selectors: {
            'discount': '[data-amrelated-js="bundle-price-discount"]',
            'finalPrice': '[data-amrelated-js="bundle-final-price"]',
            'checkbox': '[data-amrelated-js="checkbox"]',
            'packContainer': '[data-amrelated-js="pack-container"]',
            'packWrapper': '[data-amrelated-js="pack-wrapper"]',
            'packItem': '[data-amrelated-js="pack-item"]',
            'packTitle': '[data-amrelated-js="pack-title"]',
            'selectedBackground': '[data-amrelated-js="selected-background"]'

        },

        _create: function () {
            var self = this;

            $(this.element).find(this.selectors.checkbox).change(function () {
                self.changeEvent($(this));
            });

            this.observeClickOnMobile();
        },

        observeClickOnMobile: function () {
            var self = this;
            if ($(window).width() < 768) {
                $(this.element).find(this.selectors.packItem).on('click', function (event) {
                    if (!$(event.target).hasClass('amrelated-link')
                        && !$(event.target).parents().hasClass('amrelated-link')
                    ) {
                        var checkbox = $(event.target).parents(self.selectors.packItem).find(self.selectors.checkbox);

                        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                    }
                });

                $(self.element).find(this.selectors.packTitle).on('click', function (event) {
                    self.toggleItems(event);
                });
            }
        },

        toggleItems: function (event) {
            var packContainer = $(event.target).parents(this.selectors.packWrapper);

            packContainer.find(this.selectors.packTitle).toggleClass('-collapsed');
            packContainer.find(this.selectors.packItem).toggleClass('-collapsed');
        },

        changeEvent: function (checkbox) {
            var id = checkbox.val(),
                isChecked = checkbox.prop('checked'),
                packItem = checkbox.parents(this.selectors.packItem),
                isLastItem = packItem.is('.amrelated-pack-item:last-child'),
                packContainer = checkbox.parents(this.selectors.packContainer),
                itemsCount = packContainer.find(this.selectors.checkbox).length,
                packBackground = packContainer.find(this.selectors.selectedBackground),
                selectedItems = packContainer.find(this.selectors.checkbox + ':checked'),
                selectedItemsCount = selectedItems.length;

            if (isChecked) {
                packItem.addClass('-selected');
                this.excluded = this.excluded.filter(function (item) {
                    return item !== id
                });
            } else {
                packItem.removeClass('-selected');
                this.excluded.push(id);
            }

            if (packContainer.length && itemsCount > 1) {
                var rtlCondition = (isChecked && selectedItemsCount === 1) || (!isChecked && selectedItemsCount === 0);
                packBackground.toggleClass('rtl', rtlCondition ? isLastItem : !isLastItem);
            }

            if (selectedItemsCount === itemsCount) {
                packContainer.addClass('-selected');
                packBackground.width("100%");
            } else if (selectedItemsCount === 0) {
                packContainer.removeClass('-selected');
                packBackground.width(0);
            } else {
                packContainer.removeClass('-selected');
                packBackground.width(selectedItems.parents(this.selectors.packItem).outerWidth())
            }

            this.reRenderPrice();
        },

        reRenderPrice: function () {
            var self = this,
                saveAmount = 0,
                isAllUnchecked = true,
                parentPrice = +this.options.parent_price,
                oldPrice = parentPrice,
                newPrice = 0,
                $element = $(this.element),
                priceFormat = this.options.priceFormat;

            $.each(this.options.products, function (index, finalItemPrice) {
                if (self.excluded.indexOf(index) === -1) {
                    isAllUnchecked = false;
                    oldPrice += finalItemPrice;
                    newPrice += self.applyDiscount(finalItemPrice);
                }
            });

            if (isAllUnchecked) {
                newPrice = oldPrice;
            } else {
                newPrice += this.options.apply_for_parent ? this.applyDiscount(parentPrice) : parentPrice;
            }

            saveAmount = oldPrice - newPrice;

            $element.find(this.selectors.discount).html(utils.formatPrice(saveAmount, priceFormat));
            $element.find(this.selectors.finalPrice).html(utils.formatPrice(newPrice, priceFormat));
        },

        applyDiscount: function (price) {
            if (this.options.discount_type == 0) {
                price = (price > this.options.discount_amount) ? price - this.options.discount_amount : 0;
            } else {
                price = price * (100 - this.options.discount_amount) / 100;
            }

            return price;
        }
    });

    return $.mage.amPack;
});
