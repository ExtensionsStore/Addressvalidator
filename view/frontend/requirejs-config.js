/**
 * mixins
 * 
 * @category ExtensionsStore
 * @package ExtensionsStore_Addressvalidator
 * @author Extensions Store <support@extensions-store.com>
 */
var config = {
    config : {
        mixins : {
            'Magento_Checkout/js/action/set-shipping-information' : {
                'ExtensionsStore_Addressvalidator/js/action/set-shipping-information' : true
            }
        }
    }
};
