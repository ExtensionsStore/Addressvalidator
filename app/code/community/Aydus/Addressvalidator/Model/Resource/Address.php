<?php

/**
 * Address resource model
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */


class Aydus_Addressvalidator_Model_Resource_Address extends Mage_Core_Model_Resource_Db_Abstract
{
	
	protected function _construct()
	{
		$this->_init('aydus_addressvalidator/address', 'id');
	}
	
}

