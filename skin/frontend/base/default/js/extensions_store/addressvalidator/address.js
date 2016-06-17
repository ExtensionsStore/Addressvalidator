/**
 * Address Validator Billing/Shipping prototype overrides
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */

if (Billing){
	
	//replace the nextStep method in the Billing prototype
	var replaceBillingNextStepObj = {
	    nextStep: function (transport) {

	        if (transport && transport.responseText) {
	            try {
	                response = eval('(' + transport.responseText + ')');
	            }
	            catch (e) {
	                response = {};
	            }
	        }
	        
	        var addressValidatorObj = response.address_validator;

	        if (addressValidatorObj) {
	        	if (addressValidatorObj.hasOwnProperty('validate')){
	        		
	        		if (addressValidatorObj.validate === true){
	        			
	        			if (addressValidatorObj.error === false){
		                    addressValidator.validateAddress('co-billing-form', addressValidatorObj.message, addressValidatorObj.data);
	        				
	        			} else {
			                if (addressValidatorObj.data.indexOf('http') != -1) {
			                    addressValidator.redirectSupport(addressValidatorObj.message, addressValidatorObj.data);
			                } else {
			                    addressValidator.editAddress('co-billing-form', addressValidatorObj.message);
			                }
			                return false;	        				
	        			}
	                    
	        		} else if (addressValidatorObj.error === false) {
	        			addressValidator.populate('billing','co-billing-form', addressValidatorObj.data);
	        		}
    		
	        	} 
	        }

	        checkout.setStepResponse(response);

	        payment.initWhatIsCvvListeners();
	    }

	};

	Billing.addMethods(replaceBillingNextStepObj);
}

if (Shipping){
	
	//replace the nextStep method in the Shipping prototype
	var replaceShippingNextStepObj = {
	    nextStep: function (transport) {

	        if (transport && transport.responseText) {
	            try {
	                response = eval('(' + transport.responseText + ')');
	            }
	            catch (e) {
	                response = {};
	            }
	        }
	        
	        var addressValidatorObj = response.address_validator;

	        if (addressValidatorObj) {
	        	if (addressValidatorObj.hasOwnProperty('validate')){
	        		
	        		if (addressValidatorObj.validate === true){
	        			
	        			if (addressValidatorObj.error === false){
		                    addressValidator.validateAddress('co-shipping-form', addressValidatorObj.message, addressValidatorObj.data);
	        				
	        			} else {
			                if (addressValidatorObj.data.indexOf('http') != -1) {
			                    addressValidator.redirectSupport(addressValidatorObj.message, addressValidatorObj.data);
			                } else {
			                    addressValidator.editAddress('co-shipping-form', addressValidatorObj.message);
			                }
			                return false;	        				
	        			}
	                    
	        		} else if (addressValidatorObj.error === false) {
	        			addressValidator.populate('shipping','co-shipping-form', addressValidatorObj.data);
	        		}
    		
	        	} 
	        }

	        checkout.setStepResponse(response);
	    }

	};

	Shipping.addMethods(replaceShippingNextStepObj);	
}
