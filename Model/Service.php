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
	
	protected $_scopeConfig;
	protected $_services = [];
	
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\ExtensionsStore\Addressvalidator\Model\Service\Usps $usps,
			\ExtensionsStore\Addressvalidator\Model\Service\Fedex $fedex,
			\ExtensionsStore\Addressvalidator\Model\Service\Ups $ups) {
		$this->_scopeConfig = $scopeConfig;
		$this->_services[\Magento\Usps\Model\Carrier::CODE]['code'] = \Magento\Usps\Model\Carrier::CODE;
		$this->_services[\Magento\Usps\Model\Carrier::CODE]['value'] = \Magento\Usps\Model\Carrier::CODE;
		$this->_services[\Magento\Usps\Model\Carrier::CODE]['model'] = $usps;
		$this->_services[\Magento\Usps\Model\Carrier::CODE]['label'] = __('United States Postal Service');
		
		$this->_services[\Magento\Fedex\Model\Carrier::CODE]['code'] = \Magento\Fedex\Model\Carrier::CODE;
		$this->_services[\Magento\Fedex\Model\Carrier::CODE]['value'] = \Magento\Fedex\Model\Carrier::CODE;
		$this->_services[\Magento\Fedex\Model\Carrier::CODE]['model'] = $fedex;
		$this->_services[\Magento\Fedex\Model\Carrier::CODE]['label'] = __('FedEx');
		
		$this->_services[\Magento\Ups\Model\Carrier::CODE]['code'] = \Magento\Ups\Model\Carrier::CODE;
		$this->_services[\Magento\Ups\Model\Carrier::CODE]['value'] = \Magento\Ups\Model\Carrier::CODE;
		$this->_services[\Magento\Ups\Model\Carrier::CODE]['model'] = $ups;
		$this->_services[\Magento\Ups\Model\Carrier::CODE]['label'] = __('United Parcel Service');
	}
	
	public function getServiceModel($serviceCode) {
		$serviceModel = $this->_services[$serviceCode]['model'];
		return $serviceModel;
	}
	public function getServiceCodes() {
		return array_keys($this->_services);
	}
	
	public function getServices(){
		return $this->_services;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \ExtensionsStore\Addressvalidator\Api\ServiceInterface::getResults()
	 */
	public function getResults(){
		
		$serviceCode = $this->_scopeConfig->getValue('extensions_store_addressvalidator/configuration/service', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if (in_array($serviceCode, $this->_serviceCodes)){
			$serviceModel = $this->getServiceModel($serviceCode);
		}
		
		return [];
	}


}