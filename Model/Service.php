<?php

/**
 * Service model
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model;

use ExtensionsStore\Addressvalidator\Api\ServiceInterface;

class Service implements ServiceInterface {
	
	protected $_serviceCodes = ['ups','fedex','usps'];
	
	public function __construct(\ExtensionsStore\Addressvalidator\Model\Service\Usps $usps,
			\ExtensionsStore\Addressvalidator\Model\Service\Fedex $fedex,
			\ExtensionsStore\Addressvalidator\Model\Service\Ups $ups) {
		$this->_usps = $usps;
		$this->_ups = $ups;
		$this->_fedex = $fedex;
	}
	
	public function getServiceModel($serviceCode) {
		
		$serviceModel = $this->{'_'.$serviceCode};
		return $serviceModel;
	}
	public function getServiceCodes() {
		
		return $this->_serviceCodes;
	}


}