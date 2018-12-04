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
	
	/**
	 * Get service codes
	 * @return array
	 */
	public function getServiceCodes();
	
	/**
	 * Get service model by code
	 * @param string $serviceCode
	 * @return \ExtensionsStore\Addressvalidator\Api\ServiceInterface
	 */
	public function getServiceModel($serviceCode);
	
	/**
	 * Get supported services
	 * @return array
	 */
	public function getServices();
	
}