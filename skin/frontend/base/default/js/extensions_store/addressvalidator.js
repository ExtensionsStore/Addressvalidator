/**
 * Address Validator js
 * 
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */

function AddressValidator($)
{

    var config = {};
    var results = [];
    
    /**
     * Initialize popup
     * 
     */
    var initialize = function()
    {
        $('#co-billing-form .required-entry, #co-shipping-form .required-entry').change(function (e) {
        	$(this.form).find('.address-validated').val(0);
        });
        
        //close the popup and stay on the current step
        $('#av-popup .back').click(function (e) {

            var form = $('#address-form').val();
            e.preventDefault();
            e.stopPropagation();
            //allow validation
            $('#' + form).find('.address-validated').val(0);
            $('#av-popup').hide();

        });

        //handle address selection
        $('#av-popup .select').click(function (e) {

            e.preventDefault();
            e.stopPropagation();

            var $checkedRadio = $('#av-popup .radio:checked');
            var checked = ($checkedRadio.length > 0) ? true : false;

            if (checked) {

                var i = $checkedRadio.val();

                if (!isNaN(i)) {

                    var formType = getFormType();

                    i = parseInt(i);
                    var address = results[i];

                    //populate form
                    if (address) {

                        populate(formType, address);
                    }

                    $('#av-popup').hide();

                    gotoNextStep();

                }
            }

        });

        //skip address validation and continue
        $('#av-popup .skip').click(function (e) {

            e.preventDefault();
            e.stopPropagation();
            
            var form = $('#address-form').val();
            $('#' + form).append('<input type="hidden" class="skip-validation" name="skip_validation" value="1" />');

            gotoNextStep();

            $('#av-popup').hide();
        });

        //too many attempts redirect button
        $('#av-popup .okay').click(function (e) {

            e.preventDefault();
            e.stopPropagation();

            var href = $(this).attr('href');

            if (href.indexOf('http') != -1) {

                window.location.href = href;
            } else {

                window.location.href = 'customer-service';
            }
        });       	
    };
    
    /**
     * Initialize one step checkout
     */
    var initOneStepCheckout = function()
    {
        //onestepcheckout form input change
        $('#billing_address .required-entry').change(function (e) {
        	$('#billing_address').find('.address-validated').val(0);
        });
        $('#shipping_address .required-entry').change(function (e) {
            $('#shipping_address').find('.address-validated').val(0);
    	});
        
        var savingBilling, savingShipping = false;
    	
        Ajax.Responders.register({
        	onCreate : function(req, transport, json){
        		if (batriggered || satriggered){
            		if (batriggered){
            			if (savingBilling){
            				transport.abort();
            				return;
            			}
            			batriggered = false;
            			savingBilling = true;
            			req.url += '?form_id=billing_address';
            		} else if (satriggered) {
            			if (savingShipping){
            				transport.abort();
            				return;            				
            			}
            			satriggered = false;
            			savingShipping = true;
            			req.url += '?form_id=shipping_address';
            		}
        			
        		}
        	},
        	onComplete : function(req, res) {
            	if (res.readyState == 4 && res.responseText.length > 0) {
            		var response = $.parseJSON(res.responseText);
            		if (response.hasOwnProperty('address_validator')) {
            			var av = response.address_validator;
            			if (!av.error) {
            				var formId = av.form_id;
            				if (savingBilling || savingShipping){
            					if (!av.validate){
            						var formType = (formId == 'billing_address') ? 'billing' : 'shipping';
            			            results = JSON.parse(av.data);
            			            if (results.length > 0){
                						populate(formType,results[0]);
            			            }
            					} else {
                    				validateAddress(formId, av.message, av.data);
            					}
                				if (formId =='billing_address'){
                					savingBilling = false;
                				} else {
                					savingShipping = false;
                				}            					
            				}
            			}
            		}
            	}                			
        	}
        });    	
    };
    
    /**
     * select match
     * 
     * @param string form id
     * @param string message 
     * @param string resultsJson
     */
    var validateAddress = function (form, message, resultsJson)
    {
        //set the popup scope for any js that needs it
        $('#address-form').val(form);

        try {
            results = JSON.parse(resultsJson);
        } catch (e) {
            if (typeof console == 'object') {
                console.log(e.message);
            }
        }

        //create list of addresses and show popup
        if (results && results.length > 0) {
            
            //create list of address radio buttons
            var radios = '';
            $popup = getPopup();
            $popup.find('.av-message').html(message);
            //show buttons we hid in editAddress
            $popup.find('.select').show();
            var $radios = $popup.find('ul.radios');
            $radios.empty();

            var length = results.length;

            for (var i = 0; i < length; i++) {

                var address = results[i];

                var street = (typeof address.street !== 'string' && address.street.length > 1) ? address.street.join(', ') : address.street;
                var city = (typeof address.city !== 'string' && address.city.length > 1) ? address.city.join(', ') : address.city;

                var addressAr = [street, city, address.region, address.postcode, address.country];
                addressAr = addressAr.filter(function (n) {
                    return n != undefined
                });

                var label = addressAr.join(', ');
                
                var checked = (i == 0) ? 'checked="checked"' : '';

                radios += '<li><label><input type="radio" class="radio" '+checked+' name="address" value="' + i + '" /> ' + label + '</label></li>';

            }

            //stick into list
            $radios.append(radios);

            //show popup
            $('#av-popup').show();                

        }

    };   
    
    /**
     * No match, edit address
     */
    var editAddress = function (form, message)
    {
        //set the popup scope for any js that needs it
        $('#address-form').val(form);
    	
    	if (config.allow_bypass === true){
    		
            $('#' + form).append('<input type="hidden" class="address-validated" name="address_validated" value="1" />');
    		gotoNextStep();

    	} else {
    		
            //get the popup
            $popup = getPopup();
            //append message
            $popup.find('.av-message').html(message);
            //hide the select button (nothing to select) and skip button (per JM)
            $popup.find('.select, .okay').hide();
            //show popup
            $('#av-popup').show();
    	}
    };

    /**
     * Too many attempts, redirect to url
     * @param string message
     * @param string url
     */
    var redirectSupport = function (message, url)
    {
        $popup = getPopup();
        $popup.find('.av-message').html(message);
        //hide all buttons except ok
        $popup.find('.back, .select, .skip').hide();
        $popup.find('.okay').attr('href', url).show();
        //show popup
        $('#av-popup').show();
    };


    /**
     * Get reset popup
     */
    var getPopup = function ()
    {
        var $popup = $('#av-popup');
        //empty out previous results
        var $radios = $popup.find('ul.radios');
        $radios.empty();

        return $popup;
    };

    var getFormType = function()
    {
        var form = $('#address-form').val();
        var formType;
        if (form == 'co-billing-form' || form == 'billing_address') {
            formType = 'billing';
        } else if (form == 'co-shipping-form' || form == 'shipping_address') {
            formType = 'shipping';
        }    

        return formType;
    };

    /**
     * 
     * @param string formType
     * @param object address
     */
    var populate = function(formType, address)
    {
        //deselect addressbook entry
        $('#' + formType + '-new-address-form').show();
        var customerAddressId = $('#' + formType + '-address-select').val();
        var addressValidated = (customerAddressId) ? customerAddressId : 1;
        $('#' + formType + '-address-select').val(null);

        var originalStreet1 = $('#' + formType + '\\:street1').val();
        var street1 = address.street[0];
        $('#' + formType + '\\:street1').val(street1);

        if (typeof address.street[1] != 'undefined') {
            $('#' + formType + '\\:street2').val(address.street[1]);
        } else {
        	var originalStreet2 = $('#' + formType + '\\:street2').val();
        	//remove line 2 if apt number added to line 1
        	if (originalStreet2 && originalStreet1.toUpperCase().indexOf(' APT ') < 0 && street1.toUpperCase().indexOf(' APT ') >= 0){
                $('#' + formType + '\\:street2').val('');
        	}
        }
        if (typeof address.street[2] != 'undefined') {
            var street2 = $('#' + formType + '\\:street2').val();
            $('#' + formType + '\\:street2').val(street2 + ' ' + address.street[2]);
        }

        var city = (typeof address.city !== 'string' && address.city.length > 1) ? address.city.join(', ') : address.city;

        $('#' + formType + '\\:city').val(city);
        $('#' + formType + '\\:region').val(address.region);
        $('#' + formType + '\\:region_id').val(address.region_id);
        $('#' + formType + '\\:postcode').val(address.postcode);
        var $countryInput = $('#' + formType + '\\:country_id');
        $countryInput.val(address.country_id);

        $('#' + formType + '\\:save_in_address_book').attr('checked',true);  

        var addressValidatedInput = '<input type="hidden" class="address-validated input-text" name="'+formType+'[address_validated]" value="'+addressValidated+'" />';
    	var $form = $('#co-' + formType + '-form');
    	if ($form.length>0){
            var $addressValidated = $form.find('.address-validated');
            if ($addressValidated.length > 0) {
                $addressValidated.val(addressValidated);
            } else {
                $form.append(addressValidatedInput);

            }
    	} else {
    		
    		var $addressValidated = $countryInput.next();
    		if ($addressValidated.length > 0 && $addressValidated.hasClass('address-validated')){
                $addressValidated.val(addressValidated);
    		} else {
    			$countryInput.after(addressValidatedInput);
    		}
    		
    	}
        
    };

    /**
     * Go to next step
     */
    var gotoNextStep = function ()
    {
        var formId = $('#address-form').val();

        if (formId == 'co-billing-form') {
            billing.save();
        } else if (formId == 'co-shipping-form') {
            shipping.save();
        }
    };    
    
    
    return {
        
        init : function(configObj)
        {
            config = configObj;
            
            $(function () {
            	
            	initialize();
            	if ($('#onestepcheckout-form').length > 0){
                	initOneStepCheckout();
            	}
            });   
        },
        
        validateAddress : function(form, message, resultsJson)
        {
            validateAddress(form, message, resultsJson);
        },
        
        populate : function(formType, formId, data)
        {
        	if (typeof data == 'string'){
        		data = JSON.parse(data);
        	}
        	if (data.length > 0){
        		var address = data[0];
                $('#address-form').val(formId);        		
            	populate(formType, address);
                gotoNextStep();
        	}
        },
        
        editAddress : function(form, message)
        {
            editAddress(form, message);
        },
        
        redirectSupport : function (message, url)
        {
            redirectSupport(message, url);
        }
        
    };
    
}

if (!window.jQuery){
    document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js">\x3C/script><script>jQuery.noConflict();</script>');	
    document.write('<script>var addressValidator = AddressValidator(jQuery);</script>');	
} else {
    var addressValidator = AddressValidator(jQuery);
}


