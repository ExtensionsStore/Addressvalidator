<?php

/**
 * Service API interface
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Api;

interface ServiceInterface {
	
	public function getServiceCodes();
	
	public function getServiceModel($serviceCode);
	
}