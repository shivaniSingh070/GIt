/**
 * Pgrid Column Component
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'Amasty_Pgrid/js/model/column',
    'underscore'
], function (Column, amColumn, _) {
    'use strict';

    var column = _.extend({
        defaults: {
            headerTmpl: 'Amasty_Pgrid/ui/grid/columns/text'
        },
        isLinkVisible: function (record) {
            return !!this.getLabel(record);
        }
    }, amColumn);

    return Column.extend(column);
});
