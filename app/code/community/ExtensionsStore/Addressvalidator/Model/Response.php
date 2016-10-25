<?php

/**
 * Response model
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_Addressvalidator_Model_Response extends Mage_Core_Model_Abstract {
	/**
	 * Initialize resource model
	 */
	protected function _construct() {
		parent::_construct ();
		
		$this->_init ( 'extensions_store_addressvalidator/response' );
	}
}