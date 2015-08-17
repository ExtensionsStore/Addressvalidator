<?php

/**
 * Address model
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_Addressvalidator_Model_Address extends Mage_Core_Model_Abstract
{
	/**
	 * Initialize resource model
	 */
	protected function _construct()
	{
        parent::_construct();
        
		$this->_init('aydus_addressvalidator/address');
	}	
	
}