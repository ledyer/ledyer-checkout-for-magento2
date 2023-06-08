/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

define([
        'jquery',
        'uiComponent'
    ],
    function ($, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Ledyer_Payment/toggle-buttons'
            },
            b2bUrl: window.ledyerConfig.b2bUrl,
            b2cUrl: window.ledyerConfig.b2cUrl,
            b2bText: window.ledyerConfig.b2bText,
            b2cText: window.ledyerConfig.b2cText,
            url: $(location).attr('pathname').replace(/\/+$/, ''),
            initialize: function () {
                if (this.b2bUrl && this.b2cUrl && this.b2bText && this.b2cText) {
                    this._super();
                }
            },
            initButtons: function () {
                let b2cButton = $('.b2c-button'),
                    b2bButton = $('.b2b-button');
                if (this.url === this.b2bUrl.replace(/\/+$/, '')) {
                    b2bButton.addClass('active');
                } else {
                    b2cButton.addClass('active');
                }
            },
            getB2cText: function () {
                return this.b2cText;
            },
            getB2bText: function () {
                return this.b2bText;
            },
            getB2cUrl: function () {
                if (this.url === this.b2cUrl) {
                    return null;
                }
                return this.b2cUrl;
            },
            getB2bUrl: function () {
                if (this.url === this.b2bUrl) {
                    return null;
                }
                return this.b2bUrl;
            }
        });
    })
