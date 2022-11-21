/**
 * Initialize Google Tag Manager with Amasty Cookie Consent
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'mage/cookies'
], function ($, wrapper) {
    'use strict';

    return function (initializeGtm) {
        return wrapper.wrap(initializeGtm, function (originalInitializeGtm, config) {
            var disallowedCookieAmasty = $.mage.cookies.get('amcookie_disallowed') || '',
                allowedCookiesAmasty = $.mage.cookies.get('amcookie_allowed') || '',
                googleAnalyticsCookieName = '_ga';

            if ((disallowedCookieAmasty.split(',').includes(googleAnalyticsCookieName) ||
                !allowedCookiesAmasty) && window.isGdprCookieEnabled) {
                return;
            }

            originalInitializeGtm(config);
        });
    };
});
