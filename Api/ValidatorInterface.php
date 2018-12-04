<?php

/**
 * Addressvalidator API interface
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Api;

interface ValidatorInterface {
	
	/**
	 * Get service results
	 * @return [];
	 */
	public function getResults();
	
}