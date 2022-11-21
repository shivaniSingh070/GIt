/**
 * Pgrid Ajax Action
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function (type, url, data , afterSuccess, afterError, headers = {}) {
        var processData = type !== 'POST';

        $.ajax({
            url: url,
            showLoader: true,
            processData: processData,
            contentType: false,
            headers: headers,
            data: data,
            method: type,
            success: function (response) {
                afterSuccess(response);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                afterError(errorThrown);
            }
        });
    };
});
