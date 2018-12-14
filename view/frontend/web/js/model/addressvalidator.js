/**
 * Addressvalidator model
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */

define(['ko',
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/address-list',
], function (ko, $, urlBuilder, quote, storage, modal, errorProcessor, addressList) {
    'use strict';
    
    var address;
    var addressType;
    var results;
    var errorFields = [];
    var loading = false;
	var showPopupObs = ko.observable();
    
	/**
	 * Select results address
	 * @return object address
	 */
    var select = function(index){
        
        var selectedAddress = results[index];
                
        var streets = [];
        var street = selectedAddress.street[0];
        streets.push(street);
        var street2 = selectedAddress.street[1];
        if (street2){
            streets.push(street2);
        }
        var city = selectedAddress.city;
        var region = selectedAddress.region;
        var region_id = selectedAddress.region_id;
        var postcode = selectedAddress.postcode;
        var country_id = selectedAddress.country_id;
                
        address.street = streets;
        address.city = city;
        address.region = region;
        address.regionId = region_id;
        address.postcode = postcode;
        address.countryId = country_id;
        if (!address.hasOwnProperty('extensionAttributes')){
        	address.extensionAttributes = {};
        }
        address.extensionAttributes.address_validation_service = selectedAddress.service;
        address.extensionAttributes.address_validated = true;

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
                
        return address;
    };
    
    var showPopup = function(data){
        results = data.results;
        errorFields = data.error_fields;
        if (results.length>0 || (errorFields && errorFields.length>0)){
            var popupContent = data.html;
            showPopupObs(popupContent);
        }
    };
    
    var pub = {
        
        /**
         * Properties
         */
        address : address,
        addressType : addressType,
        results : results,
        errorFields : errorFields,
        showPopupObs : showPopupObs,
        
        /**
         * Get popup
         * 
         * @param {Object} address
         * @param string type Address type
         */
        getPopup: function (mutatedAddress, type) {
            
            if (!loading && mutatedAddress && mutatedAddress.street && mutatedAddress.city && mutatedAddress.regionId && mutatedAddress.region && mutatedAddress.countryId && mutatedAddress.postcode){
                
                if (address && (JSON.stringify(address.street)!=JSON.stringify(mutatedAddress.street) || 
                        address.city != mutatedAddress.city || address.region_id != mutatedAddress.region_id || 
                        address.postcode != mutatedAddress.postcode || address.country_id != mutatedAddress.country_id )){
                    mutatedAddress.extensionAttributes.address_validation_service = null;
                    mutatedAddress.extensionAttributes.address_validated = false;
                }
                address = mutatedAddress;
                addressType = type;
                
                var addressValidated = address.extensionAttributes.address_validated;
                var skipValidation = address.extensionAttributes.skip_validation;
                
                if (!skipValidation && !addressValidated){
                    
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
        },
        
        select : function(index){
        	return select(index);
        }
    };

    return pub;
});
