define([
    'underscore',
    'Amasty_Pgrid/js/action/messages'
], function (_, amMessage) {
    'use strict';

    return function (editor, deferred) {
        var valid = true,
            message = amMessage(editor),
            newValue,
            data = editor.source().get('params');

        data.amastyItems = {};
        data.store_id = editor.filters.store_id;

        _.each(_.values(editor.saveData), function (item) {
            var newValue = undefined === item.value ? '' : item.value,
                editorField = editor.getField(
                    item.entityId,
                    item.colIndex === 'category_ids' ? 'amasty_categories' : item.colIndex
                );

            if (!valid && editorField.validate().valid) {
                valid = false;

                return;
            }

            if (editorField) {
                editorField.initialValue = newValue;
            }

            if (!_.has(data.amastyItems, item.entityId)) {
                data.amastyItems[item.entityId] = {};
            }

            data.amastyItems[item.entityId][item.colIndex] = newValue;
        });

        if (valid && editor.client().busy !== true) {
            editor.client().busy = true;

            editor.columns('showLoader');

            message.clearMessages();

            editor.client()
                .save(data)
                .done(editor.onDataSaved.bind(editor, deferred))
                .fail(editor.onSaveError);
        }
    };
});
