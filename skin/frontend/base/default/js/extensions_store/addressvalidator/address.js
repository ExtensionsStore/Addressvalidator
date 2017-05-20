/**
 * Address Validator Billing/Shipping prototype overrides
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */
if (Billing) {
    //replace the nextStep method in the Billing prototype
    var replaceBillingNextStepObj = {
        nextStep: function(transport) {
            if (transport && transport.responseText) {
                try {
                    response = eval('(' + transport.responseText + ')');
                } catch (e) {
                    response = {};
                }
            }
            var addressValidatorObj = response.address_validator;
            if (addressValidatorObj) {
                addressValidator.handleResponse('co-billing-form', addressValidatorObj);
            }
            checkout.setStepResponse(response);
            payment.initWhatIsCvvListeners();
        }
    };
    Billing.addMethods(replaceBillingNextStepObj);
}
if (Shipping) {
    //replace the nextStep method in the Shipping prototype
    var replaceShippingNextStepObj = {
        nextStep: function(transport) {
            if (transport && transport.responseText) {
                try {
                    response = eval('(' + transport.responseText + ')');
                } catch (e) {
                    response = {};
                }
            }
            var addressValidatorObj = response.address_validator;
            if (addressValidatorObj) {
                addressValidator.handleResponse('co-shipping-form', addressValidatorObj);
            }
            checkout.setStepResponse(response);
        }
    };
    Shipping.addMethods(replaceShippingNextStepObj);
}