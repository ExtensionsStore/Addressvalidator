<?php

/**
 * Checkout Onepage billing override
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_Addressvalidator_Block_Checkout_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
{
    /**
     * Insert script tag before billing step, to add nextStep overrides to Billing and Shipping objects
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
    	$helper = Mage::helper('addressvalidator');
    	if ($helper->enabled()){
    		$html = '<script type="text/javascript" src="'. $this->getSkinUrl('js/extensions_store/addressvalidator/address.js').'"></script>'.$html;
    	}    	
    	
        return $html;
    }
}
