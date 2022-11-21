/**
 * Pgrid Columns Component
 */
define([
    'Magento_Ui/js/grid/controls/columns',
    'uiLayout',
    'uiRegistry',
    'ko',
    'underscore'
], function (Columns, layout, registry, ko, _) {
    'use strict';

    return Columns.extend({
        defaults: {
            selectedTab: 'tab1',
            template: 'Amasty_Pgrid/ui/grid/controls/columns',
            rowTmpl: 'Amasty_Pgrid/ui/grid/controls/row',
            sectionTmpl: 'Amasty_Pgrid/ui/grid/controls/section',
            clientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_client'
            },
            columnsData: [],
            modules: {
                client: '${ $.clientConfig.name }',
                source: '${ $.provider }',
                editorCell: '${ $.editorCellConfig.provider }',
                listingFilter: '${ $.listingFilterConfig.provider }'
            },
            imports: {
                'activeView': '${ $.parentName }.bookmarks:activeView'
            },
            listens: {
                'activeView': 'changeView'
            },
            isFirstInit: true
        },

        initialize: function () {
            this._super();

            layout([ this.clientConfig ]);

            return this;
        },

        initObservable: function () {
            this._super()
                .track([ 'selectedTab' ])
                .observe([ 'columnsData' ]);

            return this;
        },

        initElement: function (el) {
            el.track(['label', 'ampgrid_editable', 'ampgrid_filterable', 'ampgrid_title', 'ampgrid_marker']);
            el.headerTmpl = 'Amasty_Pgrid/ui/grid/columns/text';
        },

        hasSelected: function (tabKey) {
            return this.selectedTab === tabKey;
        },

        /**
         * View change listener
         *
         * @param {Object} view - view data object
         */
        changeView: function (view) {
            if (typeof view.data === 'undefined') {
                return;
            }

            if (view.index === 'default' && !this.isFirstInit) {
                this.initDefaultView();
            } else {
                this.prepareViewData(view);
            }

            this.isFirstInit = false;

            if (this.editorCell()) {
                this.prepareColumns();
                this.editorCell().model.columns('hideLoader');
            }

            this.prepareColumnsData();
        },

        /**
         * Prepare view data after change view
         *
         * @param {Object} view - view data object
         */
        prepareViewData: function (view) {
            var elementData;

            _.each(this.elems(), function (element) {
                elementData = view.data.columns[element.index];
                element.label = element.ampgrid_title || element.ampgrid.title;
                this.prepareElementViewData(elementData, element);
            }, this);
        },

        /**
         * Prepare element view data after change view
         *
         * @param {Object} elementData - view element data object
         * @param {Object} element - column element
         */
        prepareElementViewData: function (elementData, element) {
            this.changeAmpgridData(element.ampgrid, elementData);
            _.each(elementData, function (value, name) {
                if (ko.isObservable(element[name])) {
                    element[name](value);
                } else {
                    element[name] = value;
                }
            });
        },

        /**
         * Initialize default view data
         */
        initDefaultView: function () {
            _.each(this.elems(), function (element) {
                element.label = element.ampgrid_def_label;
                element.ampgrid.title = element.ampgrid_def_label;
                element.ampgrid_title = element.ampgrid_def_label;
                element.ampgrid.editable = false;
                element.ampgrid.marker = false;

                if (element.ampgrid.has_filter) {
                    element.ampgrid.filterable = true;
                }
            }, this);
        },

        /**
         * Prepare ampgrid data after view changed
         *
         * @param {Object} ampgrid - ampgrid data object
         * @param {Object} data - view data
         */
        changeAmpgridData: function (ampgrid, data) {
            const prefix = 'ampgrid_';

            _.each(data, function (value, name) {
                if (name.indexOf(prefix) !== -1) {
                    name = name.replace(prefix, '');
                }

                ampgrid[name] = value;
            });
        },

        /**
         * Split data into three columns
         * @returns {[]}
         */
        prepareColumnsData: function () {
            var self = this,
                columns = [],
                index;

            this.elems.each(function (elem) {
                index = self.getElemIndex(elem);

                if (typeof columns[index] === 'undefined') {
                    columns[index] = [];
                }

                columns[index].push(elem);
            });

            this.columnsData(columns);
        },

        getElemIndex: function (elem) {
            if (this.isDefaultColumn(elem)) {
                return 0;
            }

            if (this.isExtraColumn(elem)) {
                return 1;
            }

            if (this.isAttributeColumn(elem)) {
                return 2;
            }

            return 0;
        },

        isDefaultColumn: function (elem) {
            return elem.ampgrid && !elem.amastyExtra && !elem.amastyAttribute;
        },

        isAttributeColumn: function (elem) {
            return elem.ampgrid && elem.amastyAttribute;
        },

        isExtraColumn: function (elem) {
            return elem.ampgrid && elem.amastyExtra;
        },

        close: function () {
            return this;
        },

        /**
         * Controls current operations with grid columns
         */
        prepareColumns: function (index) {
            var columns = this,
                current,
                parentComponent,
                filter;

            columns.editorCell().model.columns('showLoader');
            this.elems.each(function (elem, currentIndex) {
                current = columns.storage().get('current.columns.' + elem.index);
                elem.label = elem.ampgrid.title;

                if (ko.isObservable(elem.ampgrid_editable)) {
                    elem.ampgrid_editable(elem.ampgrid.editable);
                }

                if (ko.isObservable(elem.ampgrid_marker)) {
                    elem.ampgrid_marker(elem.ampgrid.marker);
                }

                if (current) {
                    current.visible = elem.visible;
                    current.ampgrid_title = elem.ampgrid.title;
                    current.ampgrid_editable = elem.ampgrid.editable;
                    current.ampgrid_filterable = elem.ampgrid.filterable;
                    current.ampgrid_marker = elem.ampgrid.marker;
                }

                columns.editorCell().initColumn(elem.index);

                filter = columns.listingFilter().elems.findWhere({
                    index: elem.index
                });

                if (!filter && elem.ampgrid.filterable) {
                    elem.filter = elem.default_filter;
                    columns.listingFilter().addFilter(elem);
                }

                if (filter && !elem.ampgrid.filterable) {
                    filter.visible(false);
                } else if (filter && elem.visible && elem.ampgrid.filterable) {
                    filter.visible(true);
                }

                if (elem.index === index) {
                    parentComponent = elem.requestModule(elem.parentName);
                    parentComponent().unshiftElement(currentIndex);
                }
            });
        },

        reloadGridData: function (data) {
            var currentData;

            if (data.visible === false) {
                return this;
            }

            this.prepareColumns(data.index);

            currentData = this.source().get('params');
            currentData.data = JSON.stringify({ 'column': data.index });

            this.client()
                .save(currentData)
                .done(this.amastyReload);

            return this;
        },

        saveBookmark: function () {
            this.prepareColumns();
            this.storage().saveState();
            this.editorCell().model.columns('hideLoader');
        },

        amastyReload: function () {
            registry.get('index = product_listing').source.reload();
        }
    });
});
