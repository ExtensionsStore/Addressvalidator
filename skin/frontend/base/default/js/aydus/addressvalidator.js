/**
 * Address Validator js
 * 
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */

/**
 * Vars
 * @var array results
 */
var results;

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
    jQuery('#address-form').val(form);

    //placeholder so address doesn't get validated again
    var $addressValidated = jQuery('#' + form).find('.address-validated');
    if ($addressValidated.length > 0) {
        $addressValidated.val(1);
    } else {
        jQuery('#' + form).append('<input type="hidden" class="address-validated" name="address_validated" value="1" />');
    }

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
        $popup.find('h4').html(message);
        //show buttons we hid in editAddress
        $popup.find('.select').show();
        //hide skip button per JM
        $popup.find('.skip, .okay').hide();
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

            radios += '<li><label><input type="radio" class="radio" name="address" value="' + i + '" /> ' + label + '</label></li>';

        }

        //stick into list
        $radios.append(radios);

        //enable selection
        jQuery('.radio').click(function (e) {
            jQuery('#addressvalidator-popup .select').removeClass('disabled');
        });

        //show popup
        jQuery('#location-selector-wrapper').show();

    }

};

/**
 * No match, edit address
 */
var editAddress = function (form, message)
{
    //set the popup scope for any js that needs it
    jQuery('#address-form').val(form);

    //get the popup
    $popup = getPopup();
    //append message
    $popup.find('h4').html(message);
    //hide the select button (nothing to select) and skip button (per JM)
    $popup.find('.select, .skip, .okay').hide();
    //show popup
    jQuery('#location-selector-wrapper').show();
};

/**
 * Too many attempts, redirect to url
 * @param string message
 * @param string url
 */
var redirectSupport = function (message, url)
{
    $popup = getPopup();
    $popup.find('h4').html(message);
    //hide all buttons except ok
    $popup.find('.back, .select, .skip').hide();
    $popup.find('.okay').attr('href', url).show();
    //show popup
    jQuery('#location-selector-wrapper').show();
}


/**
 * Get reset popup
 */
var getPopup = function ()
{
    var $popup = jQuery('#addressvalidator-popup');
    //empty out previous results
    var $radios = $popup.find('ul.radios');
    $radios.empty();

    return $popup;
}

/**
 * Go to next step
 */
var gotoNextStep = function ()
{
    var form = jQuery('#address-form').val();

    if (form == 'co-billing-form') {
        billing.save();
    } else if (form == 'co-shipping-form') {
        shipping.save();
    }
}

jQuery(function () {

    jQuery('#co-billing-form input.input-text, #co-billing-form select, #co-shipping-form input.input-text, #co-shipping-form select').change(function (e) {
        jQuery(this.form).find('.address-validated').val(0);
    });

    //close the popup and stay on the current step
    jQuery('#addressvalidator-popup .back').click(function (e) {

        var form = jQuery('#address-form').val();
        e.preventDefault();
        e.stopPropagation();
        //allow validation
        jQuery('#' + form).find('.address-validated').val(0);
        jQuery('#location-selector-wrapper').hide();

    });

    //handle address selection
    jQuery('#addressvalidator-popup .select').click(function (e) {

        e.preventDefault();
        e.stopPropagation();

        var $checkedRadio = jQuery('#addressvalidator-popup .radio:checked');
        var checked = ($checkedRadio.length > 0) ? true : false;

        if (checked) {

            var i = $checkedRadio.val();

            if (!isNaN(i)) {

                var form = jQuery('#address-form').val();
                var formType;
                if (form == 'co-billing-form') {
                    formType = 'billing';
                } else if (form == 'co-shipping-form') {
                    formType = 'shipping';
                }

                i = parseInt(i);
                var address = results[i];

                //populate form
                if (address) {

                    //deselect addressbook entry
                    jQuery('#' + formType + '-new-address-form').show();
                    jQuery('#' + formType + '-address-select').val(null);

                    jQuery('#' + formType + '\\:street1').val(address.street[0]);

                    if (typeof address.street[1] != 'undefined') {
                        jQuery('#' + formType + '\\:street2').val(address.street[1]);
                    }
                    if (typeof address.street[2] != 'undefined') {
                        var street2 = jQuery('#' + formType + '\\:street2').val();
                        jQuery('#' + formType + '\\:street2').val(street2 + ' ' + address.street[2]);
                    }

                    var city = (typeof address.city !== 'string' && address.city.length > 1) ? address.city.join(', ') : address.city;

                    jQuery('#' + formType + '\\:city').val(city);
                    jQuery('#' + formType + '\\:region').val(address.region);
                    jQuery('#' + formType + '\\:region_id').val(address.region_id);
                    jQuery('#' + formType + '\\:postcode').val(address.postcode);
                    jQuery('#' + formType + '\\:country_id').val(address.country_id);
                }

                jQuery('#location-selector-wrapper').hide();

                gotoNextStep();

            }
        }

    });

    //skip address validation and continue
    jQuery('#addressvalidator-popup .skip').click(function (e) {

        e.preventDefault();
        e.stopPropagation();

        gotoNextStep();

        jQuery('#location-selector-wrapper').hide();
    });

    //too many attempts redirect button
    jQuery('#addressvalidator-popup .okay').click(function (e) {

        e.preventDefault();
        e.stopPropagation();

        var href = jQuery(this).attr('href');

        if (href.indexOf('http') != -1) {

            window.location.href = href;
        } else {

            window.location.href = 'customer-service';
        }
    });

});
