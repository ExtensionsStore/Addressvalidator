/**
 * Set shipping information mixin
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var billingAddress = quote.billingAddress();
            if (billingAddress && typeof billingAddress['extensionAttributes'] == 'undefined') {
                billingAddress['extensionAttributes'] = {};
                billingAddress['extensionAttributes']['address_validation_service'] = null;
                billingAddress['extensionAttributes']['address_validated'] = false;
                billingAddress['extensionAttributes']['skip_validation'] = false;
            }
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress && typeof shippingAddress['extensionAttributes'] == 'undefined') {
                shippingAddress['extensionAttributes'] = {};
                shippingAddress['extensionAttributes']['address_validation_service'] = null;
                shippingAddress['extensionAttributes']['address_validated'] = false;
                shippingAddress['extensionAttributes']['skip_validation'] = false;
            }
            return originalAction();
        });
    };
});
