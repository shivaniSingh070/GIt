/**
 * Cookie modal logic
 */

define([
    'uiCollection',
    'uiRegistry',
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Amasty_GdprCookie/js/model/cookie',
    'Amasty_GdprCookie/js/action/information-modal',
    'Amasty_GdprCookie/js/action/create-modal',
    'Amasty_GdprCookie/js/action/save',
    'Amasty_GdprCookie/js/action/allow',
    'Amasty_GdprCookie/js/model/cookie-data-provider',
    'Amasty_GdprCookie/js/model/manageable-cookie',
    'Amasty_GdprCookie/js/storage/essential-cookie'
], function (
    Collection,
    registry,
    $,
    _,
    $t,
    modal,
    cookieModel,
    informationModal,
    createModal,
    actionSave,
    actionAllow,
    cookieDataProvider,
    manageableCookie,
    essentialStorage
) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: {
                name: 'Amasty_GdprCookie/modal'
            },
            isDeclineEnabled: false,
            timeout: null,
            groups: [],
            cookieModal: null,
            firstShowProcess: '',
            isShowModal: false,
            showClass: '-show',
            element: {
                modal: '[data-amgdpr-js="modal"]',
                form: '[data-amcookie-js="form-cookie"]',
                container: '[data-role="gdpr-cookie-container"]',
                field: '[data-amcookie-js="field"]',
                groups: '[data-amcookie-js="groups"]',
                policy: '[data-amcookie-js="policy"]',
                settingsFooterLink: '[data-amcookie-js="footer-link"]',
                settingsGdprLink: '[data-amgdpr-js="cookie-link"]'
            },
            templates: {
                buttons: 'Amasty_GdprCookie/components/buttons',
                button: 'Amasty_GdprCookie/components/button',
                toggle: 'Amasty_GdprCookie/components/toggle',
            },
            setupModalTitle: $t('Please select and accept your Cookies Group'),
            names: {
                cookieTable: '.cookie-table',
                setupModal: '.setup-modal'
            },
            popup: {
                cssClass: 'amgdprcookie-groups-modal'
            }
        },

        initialize: function () {
            this._super();

            cookieDataProvider.getCookieData().done(function (cookieData) {
                this.groups = cookieData.groupData;
                essentialStorage.update(cookieData.groupData);
                manageableCookie.updateGroups(cookieData.groupData);
                manageableCookie.processManageableCookies();

                if (cookieModel.isShowNotificationBar(
                    this.firstShowProcess,
                    cookieData.lastUpdate
                )) {
                    this.initModal();
                }

                cookieModel.deleteDisallowedCookie();
                cookieModel.initEventHandlers();
                this.initSettingsLink();
            }.bind(this)).fail(function () {
                manageableCookie.setForce(true);
                manageableCookie.processManageableCookies();
            });

            return this;
        },

        initObservable: function () {
            this._super()
                .observe(['isShowModal']);

            return this;
        },

        /**
         * Create click event on settings links
         */
        initSettingsLink: function () {
            var elem = $(this.element.settingsFooterLink + ',' + this.element.settingsGdprLink);

            $(elem).addClass(this.showClass).on('click', this.getSettingsModal.bind(this));
        },

        /**
         * Create/open settings modal
         * @param {Event} event
         */
        getSettingsModal: function (event) {
            event.preventDefault();
            cookieDataProvider.getCookieData().done(function (data) {
                if (this.setupModal) {
                    this.setupModal.items(data.groupData);
                    this.setupModal.openModal();

                    return;
                }

                this.initSetupModal(data.groupData);
            }.bind(this));
        },

        initSetupModal: function (data) {
            createModal.call(
                this,
                data,
                '',
                this.popup.cssClass,
                false,
                'Amasty_GdprCookie/cookie-settings',
                this.name + this.names.setupModal,
                this.setupModalTitle
            );

            registry.async(this.name + this.names.setupModal)(function (modal) {
                this.setupModal = modal;
            }.bind(this));
        },

        closeModal: function () {
            if (!this.cookieModal) {
                return;
            }

            this.cookieModal.closeModal();
        },

        /**
         * On allow all cookies callback
         */
        allowCookies: function () {
            actionAllow().done(function () {
                this.closeModal();
                cookieModel.triggerAllow();
            }.bind(this));
        },

        /**
         * On save callback
         * @param {Object} element
         * @param {Object} modalContext
         */
        saveCookie: function (element, modalContext) {
            this._performSave(element, modalContext);
        },

        /**
         * Create Side Bar
         */
        initModal: function () {
            var options = {
                type: 'popup',
                responsive: true,
                modalClass: 'amgdprcookie-modal-container',
                buttons: []
            };

            this.isShowModal(true);

            this.cookieModal = modal(options, this.element.modal);

            this.cookieModal.element.html($(this.element.container));
            this.addResizeEvent();
            this.setModalHeight();
            this.cookieModal.openModal().on('modalclosed', function () {
                $(window).off('resize', this.resizeFunc);
            }.bind(this));
        },

        addResizeEvent: function () {
            this.resizeFunc = _.throttle(this.setModalHeight, 150).bind(this);
            $(window).on('resize', this.resizeFunc);
        },

        setModalHeight: function () {
            var policyHeight = $(this.element.policy).innerHeight(),
                windowHeight = window.innerHeight,
                groupsContainer = $(this.element.groups);

            if (policyHeight / windowHeight > 0.6) {
                policyHeight /= 2;
            }

            groupsContainer.height(windowHeight - policyHeight + 'px');
        },

        getInformationModal: function (data) {
            informationModal.call(this, this.names.cookieTable, data, this.popup.cssClass);
        },

        declineCookie: function (element, modalContext) {
            var formData = cookieModel.getEssentialGroups();

            this._performSave(element, modalContext, formData);
        },

        _performSave: function (element, modalContext, formData) {
            actionSave(element, formData).done(function () {
                modalContext.closeModal();
            });
        },

        focus: function (element) {
            if (element.classList.contains('-save')) {
                $(element).focus();
            }
        },
    });
});
