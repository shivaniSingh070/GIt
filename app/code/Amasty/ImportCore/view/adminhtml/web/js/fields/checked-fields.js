define([
    'uiCollection',
    'jquery',
    'ko',
    'underscore',
    'uiLayout',
    'mageUtils'
], function (Collection, $, ko, _, layout, utils) {
    'use strict';

    return Collection.extend({
        defaults: {
            checkedFields: [],
            selected: [],
            fieldsContainerSelect: '[data-amimportcore-js="fields"]',
            fieldsSelect: '[data-amimportcore-js="field"]',
            positions: [],
            selectFieldsPath: null,
            isShowFields: false,
            elemIndex: 0,
            isShowDeleteBtn: false,
            listens: {
                newCheckedField: 'addCheckedFields',
                fieldsToRemove: 'removeFields',
                elems: 'toggleBtnDelete'
            },
            exports: {
                checkedFields: '${ $.selectFieldsPath }:checkedFields',
                newCheckedField: '${ $.selectFieldsPath }:newCheckedField',
                isShowDeleteBtn: '${ $.deleteBtnPath }:visible'
            },
            imports: {
                fields: '${ $.selectFieldsPath }:fields',
                selected: '${ $.selectFieldsPath }:selected',
                newCheckedField: '${ $.selectFieldsPath }:newCheckedField',
                fieldsToRemove: '${ $.selectFieldsPath }:fieldsToRemove',
                checkedFields: '${ $.provider }:${ $.dataScope }'
            },
            modules: {
                selectFields: '${ $.selectFieldsPath }',
                deleteBtn: '${ $.deleteBtnPath }'
            }
        },

        initObservable: function () {
            this._super().observe([
                'checkedFields',
                'selected',
                'newCheckedField',
                'fieldsToRemove',
                'isShowFields',
                'isShowDeleteBtn'
            ]);

            return this;
        },

        toggleBtnDelete: function () {
            this.isShowDeleteBtn(!!this.elems().length);
        },

        removeFields: function () {
            if (this.fieldsToRemove().length) {
                this.elems.each(function (elem) {
                    if (_.contains(this.fieldsToRemove(), elem.code())) {
                        elem.remove();
                    }
                }.bind(this));

                this.fieldsToRemove([]);
            }
        },

        removeAllItems: function () {
            this.elems.each(function (elem) {
                elem.source.remove(elem.dataScope);
                elem.destroy();
            });

            this.isShowFields(false);
        },

        renderDefaultFields: function () {
            if (this.isDefaultRendered) {
                return;
            }

            _.each(this.checkedFields(), function (item) {
                this.initFields(item);
            }.bind(this));

            this.isDefaultRendered = true;
        },

        getNameField: function () {
            return this.name + '.field-' + this.elemIndex;
        },

        initFields: function (item) {
            item = this.createField(item, this.elemIndex, this.dataScope, this.getNameField());
            layout([item]);
            this.insertChild(item.name);
            this.elemIndex += 1;
        },

        createField: function (data, index, dataScope, name) {
            return utils.extend(data, {
                'name': name,
                'component': 'Amasty_ImportCore/js/fields/field',
                'provider': this.provider,
                'dataScope': dataScope + '.' + index
            });
        },

        addCheckedFields: function () {
            if (this.newCheckedField().length) {
                this.newCheckedField().forEach(function (item) {
                    this.initFields(item);
                }.bind(this));

                this.isShowFields(true);
                this.newCheckedField([]);
            }
        },

        getCheckedLength: function () {
            return Object.keys(this.checkedFields()).length;
        },

        checkFieldsState: function () {
            if (!this.getCheckedLength()) {
                this.isShowFields(false);
            }
        }
    });
});
