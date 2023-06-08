/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */
define(
    [
        'jquery',
        'mage/storage',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/action/create-shipping-address',
        'Ledyer_Payment/js/action/update-session',
        'Ledyer_Payment/js/action/get-session',
        'Magento_Customer/js/model/customer'
    ],
    function (
        $,
        storage,
        quote,
        resourceUrlManager,
        errorProcessor,
        getTotals,
        createShippingAddress,
        updateSession,
        getSession,
        customer
    ) {
        'use strict';
        return function() {
            let sessionData = getSession(),
                customerData = sessionData.customer,
                ledyerShippingAddress = customerData.shippingAddress

            if( ledyerShippingAddress ) {
                if (typeof window.ledyer !== 'undefined') {
                    window.ledyer.api.suspend();
                }
                const shippingAddressObj = {
                    ...quote.shippingAddress(),
                    company: ledyerShippingAddress.companyName,
                    countryId: ledyerShippingAddress.country,
                    city: ledyerShippingAddress.city,
                    postcode: ledyerShippingAddress.postalCode.replaceAll(' ', ''),
                    street: [ledyerShippingAddress.streetAddress || ledyerShippingAddress.companyName],
                    region: null,
                    regionId: null,
                    regionCode: null,
                    customerAddressId: null,
                    email: customerData.email,
                    firstname: customerData.firstName,
                    lastname: customerData.lastName,
                    telephone: customerData.phone,
                    extension_attributes: {
                        care_of: ledyerShippingAddress.careOf,
                        attention_name: ledyerShippingAddress.attentionName
                    }
                };
                if (!customer.isLoggedIn()) {
                    quote.guestEmail = customerData.email;
                }
                let newAddress = createShippingAddress(shippingAddressObj),
                    shippingMethod = quote.shippingMethod(),
                    methodCode = null,
                    carrierCode = null;

                if (shippingMethod) {
                    methodCode = shippingMethod['method_code'];
                    carrierCode = shippingMethod['carrier_code'];
                }

                let payload = {
                    addressInformation: {
                        'shipping_address': newAddress,
                        'billing_address': quote.billingAddress(),
                        'shipping_method_code': methodCode,
                        'shipping_carrier_code': carrierCode
                    }
                };
                storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                );
                getTotals([]).done(function () {
                    updateSession();
                    if (typeof window.ledyer !== 'undefined') {
                        window.ledyer.api.resume();
                    }
                });
                quote.shippingAddress(newAddress);
            }
        }
    }
);
