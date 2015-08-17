/**
 * Address Validator Billing/Shipping prototype overrides
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
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

	        if (response && response.validate) {
	            if (!response.error) {

	                if (typeof response.data == 'string') {
	                    addressValidator.validateAddress('co-billing-form', response.message, response.data);
	                }
	            } else {
	                if (response.data.indexOf('http') != -1) {
	                    addressValidator.redirectSupport(response.message, response.data);
	                } else {
	                    addressValidator.editAddress('co-billing-form', response.message);
	                }
	                return false;
	            }
	        }

	        if (response.error) {
	            if ((typeof response.message) == 'string') {
	                alert(response.message);
	            } else {
	                if (window.billingRegionUpdater) {
	                    billingRegionUpdater.update();
	                }

	                alert(response.message.join("\n"));
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

	        if (response && response.validate) {

	            if (!response.error) {
	                if (typeof response.data == 'string') {
	                    addressValidator.validateAddress('co-shipping-form', response.message, response.data);
	                }
	            } else {
	                if (response.data.indexOf('http') != -1) {
	                    addressValidator.redirectSupport(response.message, response.data);
	                } else {
	                    addressValidator.editAddress('co-shipping-form', response.message);
	                }
	                return false;
	            }
	        }

	        if (response.error) {
	            if ((typeof response.message) == 'string') {
	                alert(response.message);
	            } else {
	                if (window.shippingRegionUpdater) {
	                    shippingRegionUpdater.update();
	                }
	                alert(response.message.join("\n"));
	            }

	            return false;
	        }

	        checkout.setStepResponse(response);
	    }

	};

	Shipping.addMethods(replaceShippingNextStepObj);	
}
