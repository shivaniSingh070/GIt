define([
    'uiLayout',
    'mageUtils',
    'ko'
], function (layout, utils, ko) {
    'use strict';

    return function (items, title, modalClass, buttons, template, name, description, component) {
        var item = utils.extend({}, {
            'items': ko.observable(items),
            'description': description,
            'options': {
                'autoOpen': true,
                'type': 'popup',
                'title': title,
                'modalClass': modalClass,
                'buttons': buttons,
                'focus': '[data-role="action"]'
            },
            'template': template,
            'name': name,
            'component': component || 'Magento_Ui/js/modal/modal-component'
        });

        layout([item]);
        this.insertChild(item.name);
    };
});
