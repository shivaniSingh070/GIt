define([
    'Magento_Ui/js/form/element/date',
    'underscore'
], function (Input, _) {
    return Input.extend({
        defaults: {
            template: 'Amasty_ExportCore/form/element/input-date',
            dateElementTmpl: 'ui/form/element/date',
            inputElementTmpl: 'ui/form/element/input',
            inputConditions: [],
            dateNotice: '',
            inputNotice: '',
            notice: '',
            imports: {
                'parentCondition': '${ $.provider }:${ $.parentScope }.condition'
            },
            isDate: null,
            listens: {
                'parentCondition': 'conditionChanged'
            }
        },
        _initialized: false,

        initObservable: function () {
            this._super().observe(['isDate', 'parentCondition', 'notice']);

            return this;
        },
        conditionChanged: function () {
            if (_.contains(this.inputConditions, this.parentCondition())) {
                this.notice(this.dateNotice);
                this.isDate(false);
            } else {
                this.notice(this.inputNotice);
                this.isDate(true);
            }

            if (!this._initialized) {
                this._initialized = true;
                if (this.isDate()) {
                    this.onValueChange(this.value());
                }
            } else {
                this.value('');
                this.onValueChange('');
            }
        },

        onValueChange: function () {
            if (this.isDate()) {
                this._super();
            }
        }
    });
});
