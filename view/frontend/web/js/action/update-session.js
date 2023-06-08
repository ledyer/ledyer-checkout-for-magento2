/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

define([
        'jquery',
        'mage/url',
    ],
    function ($, url) {
        return function () {
            let sessionData;
            $.ajax({
                url: url.build('ledyer/session/update'),
                type: 'GET',
                contentType: 'application/json',
                async: false,
                success: function (data) {
                    sessionData = data;
                }
            });

            return sessionData;
        }
    });
