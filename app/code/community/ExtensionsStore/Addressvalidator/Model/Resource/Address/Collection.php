<?php

/**
 * Address resource collection
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_Addressvalidator_Model_Resource_Address_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
	protected function _construct() {
		parent::_construct ();
		$this->_init ( 'extensions_store_addressvalidator/address' );
	}
}