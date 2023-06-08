/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

define([
    'Ledyer_Payment/js/view/payment/method-renderer/ledyer-payment'
], function (Component) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Ledyer_Payment/ledyer'
        },
        renderLedyerIframe: function() {
            this.selectPaymentMethod();
            this._super();
        }
    });
});
