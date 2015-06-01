<?php

/**
 * Checkout Onepage billing override
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_Addressvalidator_Block_Checkout_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
{
    /**
     * Insert script tag before billing step, to add nextStep overrides to Billing and Shipping objects
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
    	$html = '<script type="text/javascript" src="'. $this->getSkinUrl('js/aydus/addressvalidator/address.js').'"></script>'.$html;
    	
        return $html;
    }
}
