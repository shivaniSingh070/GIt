/**
 * Account gift cards
 */
define([
    'underscore',
    'uiCollection',
    'jquery',
    'mage/translate',
    'Amasty_GiftCardAccount/js/action/loader',
    'Amasty_GiftCardAccount/js/model/account/cards/messages',
    'Amasty_GiftCardAccount/js/action/account-gift-code-actions'
], function (_, uiCollection, $, $t, loader, messageContainer, giftCodeActions) {
    'use strict';

    return uiCollection.extend({
        defaults: {
            addCardUrl: '',
            cardCode: '',
            isAccount: 0,
            cards: [],
            emptyFieldText: $t('Enter Gift Card Code.')
        },

        initObservable: function () {
            this._super()
                .observe(['cards', 'cardCode']);

            this.cards();
            this.loader = loader(this.isCart);

            return this;
        },

        addGiftCode: function () {
            if (!this.cardCode()) {
                messageContainer.addErrorMessage({message: this.emptyFieldText});

                return;
            }

            giftCodeActions.add(this.addCardUrl, this.cardCode()).done(function (response) {
                if (response.error) {
                    messageContainer.addErrorMessage({message: response.message});
                } else {
                    this.cards(response);
                }

                this.cardCode('');
            }.bind(this));
        },

        updateCard: function (account) {
            let items = this.cards();

            _.each(items, function (item, key) {
                if (item.id === account.id) {
                    items[key] = account;
                }
            });
            this.cards(items);
        },

        removeCard: function (id) {
            let items = this.cards(), newItems = [], i = 0;

            _.each(items, function (item) {
                if (item.id !== id) {
                    newItems[i] = item;
                    i++;
                }
            });
            this.cards(newItems);
        }
    });
});
