/**
 * Paypal OrderReviewController prototype overrides
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */
/**
 * EE 1.13
 * Dispatch an ajax request of Update Order submission
 * 
 * @param url - url where to submit shipping method
 * @param resultId - id of element to be updated
 */
OrderReviewController.prototype._submitUpdateOrder = function(event, url,
    resultId) {
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
        // add form id
        formData.form_id = 'shipping-address-form';
        // replace Ajax.Updater with Ajax.Request
        var orc = this;
        new Ajax.Request(
            url, {
                method: 'POST',
                parameters: formData,
                onComplete: function() {
                    if (this._pleaseWait && !this._updateShippingMethods) {
                        this._pleaseWait.hide();
                    }
                    this._toggleButton(this._ubpdateOrderButton, false);
                }.bind(this),
                onSuccess: function(transport) {
                    try {
                        var responseText = transport.responseText;
                        var data = JSON.parse(responseText);
                        if (data && data.hasOwnProperty('update_content')) {
                            var updateContent = data.update_content;
                            $(resultId).update(updateContent);
                            if (data.hasOwnProperty('address_validator')) {
                                var addressValidatorObj = data.address_validator;
                                if (addressValidatorObj.hasOwnProperty('validate')) {
                                    if (addressValidatorObj.validate === true) {
                                        if (addressValidatorObj.error === false) {
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
                                        addressValidator.populate(
                                            'shipping',
                                            'shipping-address-form',
                                            addressValidatorObj.data);
                                    }
                                }
                            }
                        } else if (data) {
                            $(resultId).update(data);
                        }
                    } catch (e) {
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

/**
 * EE 1.14
 * User clicked on Place Order button
 * Override button click, open Ajax connection to addressvalidator route
 * Address Validator has postdispatch event observer on the route
 * AV intercepts route response and sends back JSON with addressvalidator object containing av details
 * User can select validated address, back or skip 
 * If user selects back do nothing
 * If user selects skip, submit the form
 * If user selects address, populate hidden address form with selected address and open up Ajax connection sending user selection to update quote address
 * On Ajax completion, either submit form or show select shipping method
 */
OrderReviewController.prototype._submitOrder = function() {
    if (this._canSubmitOrder && (this.reloadByShippingSelect || this._validateForm())) {
        var addressValidated = $('address-validated').value;
        var skipValidation = $('skip-validation').value;
        if ((!addressValidated || addressValidated == '0') && (!skipValidation || skipValidation == '0')) {
            var review = this;
            var form = this.form;//paypal form
            var formData = form.serialize(true);
            var url = addressValidatorConfig.validate_url;
            //post av controller, av has observer on postdispatch
            new Ajax.Request(url, {
                method: 'POST',
                parameters: formData,
                onSuccess: function(transport) {
                    try {
                    	//hide loading
                        review._pleaseWait.hide();
                        review._updateOrderSubmit(false);
                        if (this._ubpdateOrderButton) {
                            review._ubpdateOrderButton.removeClassName('no-checkout');
                            review._ubpdateOrderButton.setStyle({
                                opacity: 1
                            });
                        }
                        var responseText = transport.responseText;
                        var data = JSON.parse(responseText);
                        if (data.hasOwnProperty('address_validator')) {
                            var addressValidatorObj = data.address_validator;
                            if (addressValidatorObj.hasOwnProperty('validate')) {
                            	//callback after address form is populated
                                var callback = function(address) {
                                    //if user clicked on skip button
                                    var skipValidation = $('skip-validation-av-popup-form');
                                    if (skipValidation) {
                                    	document.cookie = 'skip_validation=1; path=/;';
                                    	form.insert('<input type="hidden" name="skip_validation" value="1" />');
                                        form.submit();
                                    } else {
                                    	//update shipping address with selected address
                                    	if (address && address.formatted_address){
                                            var sections = $('checkoutSteps').select('.section.info-set');
                                            var section = sections[0];
                                            if (section){
                                                var ps = $(section).select('.info-box p');
                                                if (ps){
                                                    var p = ps[0];
                                                    if (p){
                                                    	p.innerText = address.formatted_address;
                                                    }                                               	
                                                }
                                            }
                                    	}
                                    	//call av controller, passing selected address
                                        var params = $('av-popup-form').serialize(true);
                                        new Ajax.Request(url, {
                                            method: 'POST',
                                            parameters: params,
                                            onComplete: function() {
                                                if (review.shippingSelect && review.shippingSelect.value != ''){
                                                    form.submit();
                                                }else {
                                                	//enable place order button
                                                    review._toggleButton(review.formSubmit, false)
                                                    var message = '<li class="error-msg"><ul><li><span>Please specify a shipping method.</span></li></ul></li>';
                                                    var messages = $('body-main').select('.messages');
                                                    if (messages.length == 0){
                                                    	var messagesUl = '<ul class="messages">'+message+'</ul>';
                                                    	$('body-main').insert({top: messagesUl});
                                                    } else {
                                                    	messages = messages[0];
                                                        messages.insert(message);
                                                    }
                                                }
                                            }.bind(this)
                                        });
                                    }
                                };
                                //show popup
                            	if (addressValidatorObj.validate === true) {
                                    if (addressValidatorObj.error === false) {
                                        //populate address form, passing callback
                                        addressValidator.validateAddress('av-popup-form', addressValidatorObj.message, addressValidatorObj.data, callback);
                                    } else {
                                    	//show error on popup or have user handle error
                                        if (addressValidatorObj.data.indexOf('http') != -1) {
                                            addressValidator.redirectSupport(addressValidatorObj.message, addressValidatorObj.data);
                                        } else {
                                            addressValidator.editAddress('av-popup-form', addressValidatorObj.message, callback);
                                        }
                                    }
                                } else if (addressValidatorObj.error === false) {
                                	//no error and not validate, so must be autopopulate 
                                    addressValidator.populate('shipping', 'av-popup-form', addressValidatorObj.data, callback);
                                }
                            }
                        } else {
                        	form.submit();
                        }
                    } catch (e) {
                        if (typeof console == 'object'){
                            console.log(e.message);
                        } 
                    }
                }
            });
        } else {
            this.form.submit();
        }
        this._updateOrderSubmit(true);
        if (this._ubpdateOrderButton) {
            this._ubpdateOrderButton.addClassName('no-checkout');
            this._ubpdateOrderButton.setStyle({
                opacity: .5
            });
        }
        if (this._pleaseWait) {
            this._pleaseWait.show();
        }
        return;
    }
    this._updateOrderSubmit(true);
};