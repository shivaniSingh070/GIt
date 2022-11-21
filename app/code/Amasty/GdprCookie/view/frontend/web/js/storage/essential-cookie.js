/**
 * Essential Cookie Storage
 */

define([
    'underscore'
], function (_) {
    'use strict';

    return {
        cookies: [],

        /**
         * Is Essential Cookie
         * @param {string} cookieName
         */
        isEssential: function (cookieName) {
            return this.cookies.indexOf(cookieName) !== -1;
        },

        /**
         * Update Essential Cookie
         * @param {array} groups
         */
        update: function (groups) {
            if (!this.cookies.length) {
                _.each(groups, function (group) {
                    if (group.isEssential) {
                        this.set(group.cookies);
                    }
                }.bind(this));
            }
        },

        set: function (cookies) {
            cookies.forEach(function (item) {
                this.cookies.push(item.name);
            }.bind(this));
        }
    };
});
