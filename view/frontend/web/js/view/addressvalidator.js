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
    'jquery', 
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/view/shipping-address/list',
    '../model/addressvalidator'
], function(ko, Component, $, utils, registry, modal, quote, shippingRatesValidator, addressConverter, addressList, addressvalidator) {
    'use strict';
    
    var showPopup = function(popupContent){
    	
    	console.log(popupContent);
        if ($('.modals-wrapper').length == 0){
        	$(document.body).append('<div class="modals-wrapper"/>');
        }
        if ($('#av-popup').length == 0){
            $('.modals-wrapper').append(popupContent);
        } else {
            $('#av-popup').replaceWith(popupContent);
        }
        
        $('#av-popup .av-result').click(select);
        
        var buttons = [];
        var backButton = {
                text: 'Back',
                attr: {
                    'data-action': 'back'
                },
                'class': 'action-primary',
                click: back
            };
        var skipButton = {
                text: 'Skip',
                attr: {
                    'data-action': 'skip_validation'
                },
                'class': 'action-primary',
                click: skipValidation
            };
        var customerServiceButton = {
                text: 'Customer Service',
                attr: {
                    'data-action': 'customer_service'
                },
                'class': 'action-primary',
                click: customerService
            };
        buttons.push(backButton, skipButton, customerServiceButton);
        
        $( "#av-popup").modal(
            {
                title: 'Address Validation',
                autoOpen: true,
                closed: closed,
                buttons: buttons
             }
        );
    };
    
    var select = function(e){
        $( "#av-popup").modal('closeModal');
        var index = $(this).val();
        index = (index) ? parseInt(index) : 0;
        var address = addressvalidator.select(index);
        var addressType = addressvalidator.addressType;
        var addressData = addressConverter.quoteAddressToFormAddressData(address);
        registry.async('checkoutProvider')(function(checkoutProvider) {
            utils.nested(checkoutProvider, addressType+'Address', addressData);
        });
        
        var formId = (addressType == 'billing') ? 'billing-new-address-form' : 'co-shipping-form';
        var $addressForm = $('#'+formId);
        var $street = $addressForm.find('input[name="street[0]"]');
        var $street2 = $addressForm.find('input[name="street[1]"]');
        var $city = $addressForm.find('input[name="city"]');
        var $regionId = $addressForm.find('select[name="region_id"]');
        var $postcode = $addressForm.find('input[name="postcode"]');
        var $countryId = $addressForm.find('select[name="country_id"]');
        
        $street.val(address.street[0]);
        if (address.street[1]){
            $street2.val(address.street[1]);
        }
        $city.val(address.city);
        $regionId.val(address.regionId);
        $postcode.val(address.postcode);
        $countryId.val(address.countryId);
    };
    
    var back = function(){
        this.closeModal();
    };
    
    var skipValidation = function(){
        this.closeModal();
        var $cookieLifetime = $('#av-popup #cookie-lifetime');
        var cookieLifetime = $cookieLifetime.val();
        cookieLifetime = (parseInt(cookieLifetime)>3600) ? parseInt(cookieLifetime) : 86400;
        cookieLifetime = cookieLifetime/ 24*60*60;
        setCookie('skip_validation',1, cookieLifetime);
        var billingAddress = quote.billingAddress();
        if (billingAddress) {
            if (typeof billingAddress.extensionAttributes == 'undefined'){
                billingAddress.extensionAttributes = {};
            }
            billingAddress.extensionAttributes['skip_validation'] = 1;
        }
        var shippingAddress = quote.shippingAddress();
        if (shippingAddress) {
            if (typeof shippingAddress.extensionAttributes == 'undefined'){
                shippingAddress.extensionAttributes = {};
            }
            shippingAddress.extensionAttributes['skip_validation'] = 1;
        }
    };
    
    var customerService = function(){
        var $customerServiceUrl = $('#customer-service-url');
        var customerServiceUrl = $customerServiceUrl.val();
        
        var url = urlBuilder.build(customerServiceUrl);
        window.location.href = url;
    };
    
    var closed = function(e){
        if (addressvalidator.errorFields && addressvalidator.errorFields.length>0){
            var formId = (addressType == 'billing') ? 'billing-new-address-form' : 'co-shipping-form';
            var $addressForm = $('#'+formId);
            for (var i=0; i<errorFields.length; i++){
                var errorField = addressvalidator.errorFields[i];
                var $errorField = $addressForm.find('input[name="'+errorField+'"]');
                if (i == 0){
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $errorField.offset().top - 50
                    }, 500);
                }
                var $field = $errorField.closest('.field');
                $field.addClass('_error');
                $errorField.on('change',errorFieldUpdated);
        	}
        }
    };
    
    var errorFieldUpdated = function(e){
        var $errorField = $(this);
        var $field = $errorField.closest('.field');
        var updatedValue = $errorField.val();
        if (updatedValue != ''){
            var name = $errorField.attr('name');
            var index = addressvalidator.errorFields.indexOf(name);
            if (index !== -1) {
            	addressvalidator.errorFields.splice(index, 1);
            }
            $field.removeClass('_error');
            $errorField.off('change');
        } else {
            $field.addClass('_error');
        }
        
        if (addressvalidator.errorFields.length==0){
            shippingRatesValidator.validateFields();
        }
    };
    
    //https://stackoverflow.com/questions/14573223/set-cookie-and-get-cookie-with-javascript
    var setCookie = function(name, value, days){
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    };
    var getCookie = function(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    };
    var eraseCookie = function(name) {   
        document.cookie = name+'=; Max-Age=-99999999;';  
    };
    
    return Component.extend({
        initialize: function () {
            this._super();
            if (window.checkoutConfig.addressvalidator && window.checkoutConfig.addressvalidator.configuration.enabled == '1'){
            
                var getPopup = function (mutatedAddress, addressType) {
                    if (typeof mutatedAddress.customAttributes == 'undefined'){
                        mutatedAddress.customAttributes = {};
                    }
                    if (typeof mutatedAddress.extensionAttributes == 'undefined') {
                        mutatedAddress.extensionAttributes = {};
                        mutatedAddress.extensionAttributes.address_validation_service = null;
                        mutatedAddress.extensionAttributes.address_validated = null;
                        mutatedAddress.extensionAttributes.skip_validation = (getCookie('skip_validation') != null) ? getCookie('skip_validation') : false;
                    }
                    addressvalidator.getPopup(mutatedAddress, addressType);
                };
            	
                quote.shippingAddress.subscribe(function(mutatedAddress){
                	getPopup(mutatedAddress, 'shipping');
                });
                quote.billingAddress.subscribe(function(mutatedAddress){
                	getPopup(mutatedAddress, 'billing');
                });
                
                addressvalidator.showPopupObs.subscribe(showPopup);
            }
            
            return this;
        },
    });
});
