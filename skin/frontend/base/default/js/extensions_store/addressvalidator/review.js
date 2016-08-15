/**
 * Paypal OrderReviewController prototype overrides
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */

/**
 * Dispatch an ajax request of Update Order submission
 * @param url - url where to submit shipping method
 * @param resultId - id of element to be updated
 */
OrderReviewController.prototype._submitUpdateOrder = function(event, url, resultId)
{
    this._copyShippingToBilling();
    if (url && resultId && this._validateForm()) {
        if (this._copyElement && this._copyElement.checked) {
            this._clearValidation('billing');
        }
        this._updateOrderSubmit(true);
        if (this._pleaseWait) {
            this._pleaseWait.show();
        }
        this._toggleButton(this._ubpdateOrderButton, true);

        arr = $$('[id^="billing:"]').invoke('enable');
        var formData = this.form.serialize(true);
        if (this._copyElement.checked) {
            $$('[id^="billing:"]').invoke('disable');
            this._copyElement.enable();
        }
        formData.isAjax = true;
        //add form id
        formData.form_id = 'shipping-address-form';
        //replace Ajax.Updater with Ajax.Request
        var orc = this;
        new Ajax.Request(url,{
        	method: 'POST',
            parameters: formData,
            onComplete: function() {
                if (this._pleaseWait && !this._updateShippingMethods) {
                    this._pleaseWait.hide();
                }
                this._toggleButton(this._ubpdateOrderButton, false);
            }.bind(this),            
            onSuccess: function(transport){
            	try {
            		var responseText = transport.responseText;
                	var data = JSON.parse(responseText);
                	if (data && data.hasOwnProperty('update_content')){
                    	var updateContent = data.update_content;
                    	$(resultId).update(updateContent);
                    	if (data.hasOwnProperty('address_validator')){
                    		var addressValidatorObj = data.address_validator;
            	        	if (addressValidatorObj.hasOwnProperty('validate')){
            	        		
            	        		if (addressValidatorObj.validate === true){
            	        			
            	        			if (addressValidatorObj.error === false){
            		                    addressValidator.validateAddress('shipping-address-form', addressValidatorObj.message, addressValidatorObj.data, orc._copyShippingToBilling.bind(orc));
            	        			} else {
            			                if (addressValidatorObj.data.indexOf('http') != -1) {
            			                    addressValidator.redirectSupport(addressValidatorObj.message, addressValidatorObj.data);
            			                } else {
            			                    addressValidator.editAddress('shipping-address-form', addressValidatorObj.message);
            			                }
            			                return false;	        				
            	        			}
            	                    
            	        		} else if (addressValidatorObj.error === false) {
            	        			addressValidator.populate('shipping','shipping-address-form', addressValidatorObj.data);
            	        		}
            	        	}             		
                    	}            		
                	} else if (data) {
                		$(resultId).update(data);
                	}            		
            		
            	}catch(e){
            		$(resultId).update(responseText);
            	}
            	
            	orc._updateShippingMethodsElement();
            },
            evalScripts: true
        });

    } else {
        if (this._copyElement && this._copyElement.checked) {
            this._clearValidation('billing');
        }
    }
};