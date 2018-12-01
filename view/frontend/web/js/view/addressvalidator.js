/**
 * Addressvalidator component
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */

define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'ExtensionsStore_Addressvalidator/js/model/addressvalidator'
], function(ko, Component, quote, addressvalidator) {
    'use strict';
    return Component.extend({
        initialize: function () {

            this._super();
            
            quote.shippingAddress.subscribe(function (address) {
            	addressvalidator.getPopup(address, 'shipping');
            });

            quote.billingAddress.subscribe(function (address) {
            	addressvalidator.getPopup(address, 'billing');
            });
            
            return this;
        },


    });
});
