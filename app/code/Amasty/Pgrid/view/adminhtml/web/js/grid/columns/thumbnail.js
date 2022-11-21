/**
 * Pgrid Thumbnail Component
 */
define([
    'jquery',
    'Magento_Ui/js/grid/columns/thumbnail',
    'Amasty_Pgrid/js/model/column',
    'Magento_Ui/js/modal/modal',
    'underscore',
    'Amasty_Pgrid/js/action/messages',
    'mage/translate',
    'Amasty_Pgrid/js/action/ajax',
    'prototype'
], function ($, Thumbnail, amColumn, modal, _, amMessage, $t, actionAjax) {
    'use strict';

    var column = _.extend({
        defaults: {
            modalUrl: '',
            saveUrl: '',
            modalComponent: '',
            selectors: {
                imagesData: '[data-form-part="product_form"]',
                formKey: '[name="form_key"]'
            },
            cssClasses: {
                modal: 'ampgrid-modal-image',
                primery: 'action-primary'
            },
            text: {
                linkDetail: $t('Go to Details Page'),
                upload: $t('Upload Images and Videos for '),
                save: $t('Save')
            },
            imports: {
                namespace: '${ $.parentName }:ns'
            },
            modules: {
                parent: '${ $.parentName }',
                source: '${ $.provider }',
                editor: '${ $.parentName }_amasty_editor'
            }
        },

        /**
         * After click on image
         *
         * @param {object} row - row data
         */
        preview: function (row) {
            if (!this.ampgrid_editable()) {
                return this._super();
            }

            this.productId = row.entity_id;

            return this.geEditModalHtml(row);
        },

        /**
         * Get edit images modal html
         *
         * @param {object} row - row data
         */
        geEditModalHtml: function (row) {
            var data = { entity_id: row.entity_id };

            actionAjax(
                'GET',
                this.modalUrl,
                data,
                this.showEditImageModal.bind(this, row.name),
                this.editor().onSaveError
            );
        },

        /**
         * Create edit image modal
         *
         * @param {string} name - product name
         * @param {string} modalHtml - modal html
         */
        showEditImageModal: function (name, modalHtml) {
            var self = this;

            this.previewPopup = $('<div/>').html(modalHtml);

            this.previewPopup.modal({
                title: this.text.upload + '"' + name + '"',
                innerScroll: true,
                type: 'slide',
                modalClass: this.cssClasses.modal,
                buttons: [ {
                    text: this.text.save,
                    class: this.cssClasses.primery,
                    click: function () {
                        self.actionSave();
                    }
                } ],
                closed: function () {
                    this.closest('.ampgrid-modal-image').remove();
                }
            }).trigger('openModal');
        },

        /**
         * Prepare data for saving
         */
        actionSave: function () {
            var formData = new FormData(),
                name = 'amastyItems[' + this.productId + ']',
                inputs = this.previewPopup.find(this.selectors.imagesData),
                data = this.source().get('params');

            this.getParamsToFormData(data, formData);

            formData.append('form_key', $(this.selectors.formKey).val());
            formData.append('store_id', 0);
            formData.append('namespace', this.namespace);
            _.each(inputs, function (input) {
                formData.append(input.name.replace('product', name), input.value);
            });

            this.saveImages(formData);
        },

        /**
         * Prepare source params
         *
         * @param {object} data - params
         * @param {object} formData - formData
         * @param {string} recKey - key prefix
         */
        getParamsToFormData: function (data, formData, recKey) {
            _.each(data, function (value, key) {
                key = recKey ? recKey + '[' + key + ']' : key;

                if (_.isObject(value)) {
                    return this.getParamsToFormData(value, formData, key);
                }

                formData.append(key, value);
            }.bind(this));
        },

        /**
         * After save success callback
         *
         * @param {string} response - modal html
         */
        afterSaveSuccess: function (response) {
            this.previewPopup.modal('closeModal');
            this.editor().onDataSaved(false, response);
        },

        /**
         * After save error callback
         *
         * @param {string} errorThrown - text error
         */
        afterSaveError: function (errorThrown) {
            this.previewPopup.modal('closeModal');
            this.editor().onSaveError(errorThrown);
        },

        /**
         * Save method call
         *
         * @param {object} formData - formData
         */
        saveImages: function (formData) {
            actionAjax(
                'POST',
                this.saveUrl,
                formData,
                this.afterSaveSuccess.bind(this),
                this.afterSaveError.bind(this),
                { 'Accept': 'application/json' }
            );
        }
    }, amColumn);

    return Thumbnail.extend(column);
});
