<?php
/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model\ResourceModel;

class Validator extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	protected function _construct() {
		$this->_init ('extensions_store_addressvalidator', 'id');
	}
}
