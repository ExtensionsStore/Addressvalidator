<?php

/**
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	protected function _construct() {
		$this->_init ( \ExtensionsStore\Addressvalidator\Model\Validator::class, \ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator::class );
	}
}