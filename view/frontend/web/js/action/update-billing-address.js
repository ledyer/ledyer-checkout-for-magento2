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
        'Ledyer_Payment/js/action/update-session',
        'Ledyer_Payment/js/action/get-session'
    ],
    function (
        $,
        storage,
        quote,
        resourceUrlManager,
        errorProcessor,
        updateSession,
        getSession
    ) {
        'use strict';
        return function() {
            window.ledyer.api.suspend();
            let sessionData = getSession(),
                customerData = sessionData.customer,
                ledyerBillingAddress = customerData.billingAddress,
                defaultBilling = quote.billingAddress() || quote.shippingAddress()
            const billingAddressObj = {
                ...defaultBilling,
                company: ledyerBillingAddress.companyName,
                countryId: ledyerBillingAddress.country,
                city: ledyerBillingAddress.city,
                postcode: ledyerBillingAddress.postalCode.replaceAll(' ', ''),
                street: [ledyerBillingAddress.streetAddress || ledyerBillingAddress.companyName],
                region: null,
                regionId: null,
                regionCode: null,
                customerAddressId: null,
                email: customerData.email,
                firstname: customerData.firstName,
                lastname: customerData.lastName,
                telephone: customerData.phone,
                extension_attributes: {
                    care_of: ledyerBillingAddress.careOf,
                    attention_name: ledyerBillingAddress.attentionName
                }
            };
            let shippingMethod = quote.shippingMethod(),
                methodCode = null,
                carrierCode = null;
            if (shippingMethod) {
                methodCode = shippingMethod['method_code'];
                carrierCode = shippingMethod['carrier_code'];
            }
            let payload = {
                addressInformation: {
                    'shipping_address': quote.shippingAddress(),
                    'billing_address': billingAddressObj,
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
            quote.billingAddress(billingAddressObj);
            window.ledyer.api.resume();
        }
    }
);
