/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

define([
    'Magento_Checkout/js/view/payment/default',
    'Ledyer_Payment/js/action/update-shipping-address',
    'Ledyer_Payment/js/action/update-billing-address',
    'Ledyer_Payment/js/action/create-session',
    'Magento_Checkout/js/model/quote'
], function (Component, updateShipping, updateBilling, createSession, quote) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Ledyer_Payment/payment/ledyer-payment'
        },
        renderLedyerIframe: function() {
            let data = createSession();
            const s = document.createElement('script');
            s.setAttribute('src', data.url);
            s.setAttribute('data-env', data.env);
            s.setAttribute('data-session-id',  data.sessionId);
            s.setAttribute('data-container-id', data.containerId);
            if (data.buttonColor) {
                s.setAttribute('data-buy-button-color', data.buttonColor);
            }
            document.body.appendChild(s);
            document.addEventListener('ledyerCheckoutBillingUpdated', (event) => {
                updateShipping();
                updateBilling();
            });
            document.addEventListener('ledyerCheckoutShippingUpdated', (event) => {
                updateShipping();
                updateBilling();
            });
            document.addEventListener('ledyerCheckoutCustomerUpdated', (event) => {
                updateShipping();
                updateBilling();
            });
            document.addEventListener('ledyerCheckoutOrderComplete', (event) => {
                updateBilling();
                this.placeOrder(this, event);
            });
            document.addEventListener('ledyerCheckoutOrderPending', (event) => {
                updateBilling();
                this.placeOrder(this, event);
            });
        }
    });
});
