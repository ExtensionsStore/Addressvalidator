<?php
/**
 *
 * Address Validator controller
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_Addressvalidator_IndexController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		$this->loadLayout ()->renderLayout ();
	}
	public function validateAction() {
		$result = array (
				'error' => false,
				'data' => null 
		);
		$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
	}
}