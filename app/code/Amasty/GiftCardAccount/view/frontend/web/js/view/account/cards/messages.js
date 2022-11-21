define([
    'Magento_Ui/js/view/messages',
    'Amasty_GiftCardAccount/js/model/account/cards/messages'
], function (Component, messageContainer) {
    'use strict';

    return Component.extend({

        initialize: function (config) {
            return this._super(config, messageContainer);
        }
    });
});
