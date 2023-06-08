/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

define([
        'jquery',
        'Magento_Checkout/js/view/shipping',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/set-shipping-information',
        'uiRegistry',
        'Ledyer_Payment/js/action/update-shipping-address',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method'
    ],
    function(
        $,
        Component,
        quote,
        checkoutDataResolver,
        checkoutData,
        setShippingInformationAction,
        registry,
        updateShipping,
        shippingService,
        selectShippingMethodAction
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Ledyer_Payment/shipping-selector',
                shippingMethodItemTemplate: 'Ledyer_Payment/shipping-method-item'
            },
            setShippingInformation: function () {
                checkoutDataResolver.resolveBillingAddress();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                });
                setShippingInformationAction().done(
                    function () {
                      updateShipping();
                    }
                );
            },
            preselectFirstShippingMethod: function() {
                setTimeout(function () {
                    let methods = shippingService.getShippingRates()();
                    if (!quote.shippingMethod() && methods.length > 0) {
                        selectShippingMethodAction(methods[0]);
                    }
                }, 500);
            }
        });
    });
