/**
 * Address Validator Billing/Shipping prototype overrides
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */

if (window.shipping){
	
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

	        if (addressValidatorObj && addressValidatorObj.validate) {
	            if (!addressValidatorObj.error) {

	                if (typeof addressValidatorObj.data == 'string') {
	                    addressValidator.validateAddress('co-billing-form', addressValidatorObj.message, addressValidatorObj.data);
	                }
	            } else {
	                if (addressValidatorObj.data.indexOf('http') != -1) {
	                    addressValidator.redirectSupport(addressValidatorObj.message, addressValidatorObj.data);
	                } else {
	                    addressValidator.editAddress('co-billing-form', addressValidatorObj.message);
	                }
	                return false;
	            }
	        }

	        if (addressValidatorObj && addressValidatorObj.error) {
	            if ((typeof addressValidatorObj.message) == 'string') {
	                alert(addressValidatorObj.message);
	            } else {
	                if (window.billingRegionUpdater) {
	                    billingRegionUpdater.update();
	                }

	                alert(addressValidatorObj.message.join("\n"));
	            }

	            return false;
	        }

	        checkout.setStepResponse(response);

	        payment.initWhatIsCvvListeners();
	    }

	};
	
	Billing.addMethods(replaceBillingNextStepObj);
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

	        if (addressValidatorObj && addressValidatorObj.validate) {

	            if (!addressValidatorObj.error) {
	                if (typeof addressValidatorObj.data == 'string') {
	                    addressValidator.validateAddress('co-shipping-form', addressValidatorObj.message, addressValidatorObj.data);
	                }
	            } else {
	                if (response.data.indexOf('http') != -1) {
	                    addressValidator.redirectSupport(addressValidatorObj.message, addressValidatorObj.data);
	                } else {
	                    addressValidator.editAddress('co-shipping-form', addressValidatorObj.message);
	                }
	                return false;
	            }
	        }

	        if (addressValidatorObj && addressValidatorObj.error) {
	            if ((typeof addressValidatorObj.message) == 'string') {
	                alert(addressValidatorObj.message);
	            } else {
	                if (window.shippingRegionUpdater) {
	                    shippingRegionUpdater.update();
	                }
	                alert(addressValidatorObj.message.join("\n"));
	            }

	            return false;
	        }

	        checkout.setStepResponse(response);
	    }

	};

	Shipping.addMethods(replaceShippingNextStepObj);	
}
