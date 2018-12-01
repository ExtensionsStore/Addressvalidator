/**
 * Addressvalidator model
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */

define([
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'mage/storage',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/error-processor'
], function ($, urlBuilder, quote, shippingRatesValidator, storage, modal, errorProcessor) {
    'use strict';
    
    var address;
    var addressType;
    var results;
    var loading = false;
    var errorField = null;
    
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
    
    var select = function(e){
        var index = $(this).val();
        index = parseInt(index);
        
        var selectedAddress = results[index];
                
        var streets = [];
        var street = selectedAddress.street[0];
        streets.push(street);
        var street2 = selectedAddress.street[1];
        if (street2){
            streets.push(street2);
        }
        var city = selectedAddress.city;
        var region_id = selectedAddress.region_id;
        var postcode = selectedAddress.postcode;
        var country_id = selectedAddress.country_id;
        
        var formId = (addressType == 'billing') ? 'billing-new-address-form' : 'co-shipping-form';
        var $addressForm = $('#'+formId);
        var $street = $addressForm.find('input[name="street[0]"]');
        var $street2 = $addressForm.find('input[name="street[1]"]');
        var $city = $addressForm.find('input[name="city"]');
        var $regionId = $addressForm.find('select[name="region_id"]');
        var $postcode = $addressForm.find('input[name="postcode"]');
        var $countryId = $addressForm.find('select[name="country_id"]');
        
        $street.val(street).change();
        if (street2){
            $street2.val(street2).change();
        }
        $city.val(city).change();
        $regionId.val(region_id).change();
        $postcode.val(postcode).change();
        $countryId.val(country_id).change();
        
        address.street = streets;
        address.city = city;
        address.regionId = region_id;
        address.postcode = postcode;
        address.countryId = country_id;
        address.extensionAttributes.address_validation_service = selectedAddress.service;
        address.extensionAttributes.address_validated = true;
        
        (addressType =='billing') ? quote.billingAddress(address) : quote.shippingAddress(address);
        loading = true;
        var updateUrl = urlBuilder.build('addressvalidator/ajax/update');
        var payload = JSON.stringify({
        addressvalidator: {
                    'address_type': addressType,
                    'address_validated': address.extensionAttributes.address_validated,
                    'address_validation_service': address.extensionAttributes.address_validation_service,
                    'address': {
                        'customer_address_id' : address.customerAddressId,
                        'company': address.company,
                        'street' : address.street,
                        'city' : address.city,
                        'region' : address.region,
                        'region_code' : address.regionCode,
                        'region_id' : address.regionId,
                        'postcode': address.postcode,
                        'country_id': address.countryId
                    }
                }
            }
        );
        storage.post(
             updateUrl, payload, false
                ).done(function(response){
                    if (!response.error){
                    } else {
                        errorProcessor.process(response.error);
                    }
                }).fail(function (response) {
                    errorProcessor.process(response);
                }).always(function () {
                    loading = false;
                });
                
        $( "#av-popup").modal('closeModal');
    };
    
    var closed = function(e){
        if (errorField){
            var formId = (addressType == 'billing') ? 'billing-new-address-form' : 'co-shipping-form';
            var $addressForm = $('#'+formId);
            var $errorField = $addressForm.find('input[name="'+errorField+'"]');
            var $field = $errorField.closest('.field');
            $field.addClass('_error');
            $errorField.change(errorFieldUpdated);
            $([document.documentElement, document.body]).animate({
                scrollTop: $errorField.offset().top - 50
            }, 500);
        }
    };
    
    var errorFieldUpdated = function(e){
        if (errorField){
            errorField = null;
            var $errorField = $(this);
            var $field = $errorField.closest('.field');
            var updatedValue = $errorField.val();
            if (updatedValue != ''){
                $field.removeClass('_error');
                shippingRatesValidator.validateFields();
            } else {
                $field.addClass('_error');
            }
        }
    };
    
    var showPopup = function(data){
        results = data.results;
        var popupContent = data.html;
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
        
        if ($('#av-popup .av-error-message').length > 0){
            var errorMessage = $('#av-popup .av-error-message').text();
            errorField = (errorMessage.length > 0 && errorMessage.search(/apartment|apt/i)>=0) ? 'street[1]' : null;
            errorField = (!errorField && errorMessage.length > 0 && errorMessage.search(/street/i)>=0) ? 'street[0]' : errorField;
            errorField = (!errorField && errorMessage.length > 0 && errorMessage.search(/city/i)>=0) ? 'city' : errorField;
            errorField = (!errorField && errorMessage.length > 0 && errorMessage.search(/state|region/i)>=0) ? 'region_id' : errorField;
            errorField = (!errorField && errorMessage.length > 0 && errorMessage.search(/postcode/i)>=0) ? 'postcode' : errorField;
            errorField = (!errorField && errorMessage.length > 0 && errorMessage.search(/country/i)>=0) ? 'country_id' : errorField;
        }
        
        $( "#av-popup").modal(
            {
                title: 'Address Validation',
                autoOpen: true,
                closed: closed,
                buttons: buttons
             }
        );
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
    
    var publicMethods = {
        
        /**
         * Get popup
         * 
         * @param {Object} address
         * @param string type Address type
         */
        getPopup: function (mutatedAddress, type) {
            
            if (!loading && mutatedAddress && mutatedAddress.street && mutatedAddress.city && mutatedAddress.regionId && mutatedAddress.region && mutatedAddress.countryId && mutatedAddress.postcode){
                
                if (typeof mutatedAddress.customAttributes == 'undefined'){
                    mutatedAddress.customAttributes = {};
                    mutatedAddress.customAttributes['address_validated'] = false;
                }
                if (typeof mutatedAddress.extensionAttributes == 'undefined') {
                    mutatedAddress.extensionAttributes = {};
                    mutatedAddress.extensionAttributes['address_validation_service'] = null;
                    mutatedAddress.extensionAttributes['address_validated'] = false;
                    mutatedAddress.extensionAttributes['skip_validation'] = (getCookie('skip_validation') != null) ? getCookie('skip_validation') : false;
                }
                
                if (address && (JSON.stringify(address.street)!=JSON.stringify(mutatedAddress.street) || 
                        address.city != mutatedAddress.city || address.region_id != mutatedAddress.region_id || 
                        address.postcode != mutatedAddress.postcode || address.country_id != mutatedAddress.country_id )){
                    mutatedAddress.extensionAttributes['address_validation_service'] = null;
                    mutatedAddress.extensionAttributes['address_validated'] = false;
                }
                address = mutatedAddress;
                addressType = type;
                
                var addressValidated = address.customAttributes['address_validated'];
                addressValidated = (addressValidated) ? addressValidated : address.extensionAttributes['address_validated'];
                var skipValidation = address.extensionAttributes['skip_validation'];
                
                if (skipValidation == false && addressValidated == false){
                    
                    loading = true;
                    
                    var popupUrl = urlBuilder.build('addressvalidator/ajax/popup');
                    address.email = (address.email) ? address.email : quote.guestEmail;
                    var payload = JSON.stringify({
                    addressvalidator: {
                                'address_type': type,
                                'street': address.street,
                                'city': address.city,
                                'region_id': address.regionId,
                                'region': address.region,
                                'country_id': address.countryId,
                                'postcode': address.postcode,
                                'email': address.email,
                                'customer_id': address.customerId,
                                'customer_address_id': address.customerAddressId,
                                'firstname': address.firstname,
                                'lastname': address.lastname,
                                'middlename': address.middlename,
                                'prefix': address.prefix,
                                'suffix': address.suffix,
                                'vat_id': address.vatId,
                                'company': address.company,
                                'telephone': address.telephone,
                                'fax': address.fax,
                                'custom_attributes': address.customAttributes,
                                'extension_attributes' : address.extensionAttributes,
                                'save_in_address_book': address.saveInAddressBook
                            }
                        }
                    );
                    
                    storage.post(
                        popupUrl, payload, false
                        ).done(function(response){
                            if (!response.error){
                                showPopup(response.data);
                            } else {
                                errorProcessor.process(response.error);
                            }
                        }).fail(function (response) {
                            errorProcessor.process(response);
                        }).always(function () {
                            loading = false;
                        });
                }
            }
        }
    };

    return publicMethods;
});
