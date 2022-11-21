/**
 * Pgrid Select Element
 */

define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            listens: {
                value: 'checkValue'
            }
        },

        checkValue: function (value) {
            if (this.initialValue !== value) {
                window.isPgridEditable = true;
            }
        }
    });
});
