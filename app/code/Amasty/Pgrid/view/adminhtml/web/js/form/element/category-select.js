/**
 * Pgrid Category Select
 */
define([
    'Magento_Catalog/js/components/new-category',
    'underscore'
], function (Select, _) {
    'use strict';

    return Select.extend({
        defaults: {
            valuesSize: null,
            listens: {
                'value': 'prepareValue'
            }
        },

        initialize: function () {
            this._super();

            this.valuesSize = this.value().size();

            return this;
        },

        initObservable: function () {
            this._super()
                .observe([ '$index' ]);

            return this;
        },

        /**
         * prepares the ID array
         *
         * @param {array} values - array selected values
         */
        prepareValue: function (values) {
            var preparedValues = values.map(function (item) {
                if (_.isObject(item)) {
                    return item.id;
                }

                return item;
            });

            if (_.difference(preparedValues, values).length) {
                this.value(preparedValues);
            }

            if (this.valuesSize !== null && this.valuesSize !== values.size()) {
                window.isPgridEditable = true;
            }
        },

        /**
         * Reports that the category selection has been changed
         *
         * @returns {Boolean}
         */
        hasChanged: function () {
            return !_.isEqual(_.sortBy(this.initialValue), _.sortBy(this.value()));
        },

        outerClick: function () {
            this._super();

            this.focused(false);
        },

        onFocusOut: function () {
            this._super();

            this.focused(false);
        },

        onFocusIn: function () {
            this._super();

            this.focused(true);
        }
    });
});
