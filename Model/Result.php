<?php

/**
 * Result model
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model;

class Result extends \Magento\Framework\Model\AbstractModel implements \ExtensionsStore\Addressvalidator\Api\Data\ResultInterface {
	protected $_scopeConfig;
	protected $_error;
	protected $_errorMessage;
	protected $_service;
	protected $_serviceLabel;
	protected $_showService;
	protected $_apartmentRequired;
	protected $_isCommercial;
	/**
	 *
	 * @return bool
	 */
	public function getError() {
		return $this->_error;
	}
	
	/**
	 *
	 * @param bool $error
	 * @return $this
	 */
	public function setError($error) {
		$this->_error = $error;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->_errorMessage;
	}
	
	/**
	 *
	 * @param string $errorMessage
	 * @return $this
	 */
	public function setErrorMessage($errorMessage) {
		$this->_errorMessage = $errorMessage;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getService() {
		return $this->_service;
	}
	
	/**
	 *
	 * @param string $service
	 * @return $this
	 */
	public function setService($service) {
		$this->_service = $service;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getServiceLabel() {
		return $this->_serviceLabel;
	}
	
	/**
	 *
	 * @param string $serviceLabel
	 * @return $this
	 */
	public function setServiceLabel($serviceLabel) {
		$this->_serviceLabel = $serviceLabel;
		return $this;
	}
	
	/**
	 *
	 * @return bool
	 */
	public function getShowService() {
		return $this->_showService;
	}
	
	/**
	 *
	 * @param bool $showService
	 * @return $this
	 */
	public function setShowService($showService) {
		$this->_showService = $showService;
		return $this;
	}
	
	/**
	 *
	 * @return bool
	 */
	public function getApartmentRequired() {
		return $this->_apartmentRequired;
	}
	
	/**
	 *
	 * @param bool $apartmentRequired
	 * @return $this
	 */
	public function setApartmentRequired($apartmentRequired) {
		$this->_apartmentRequired = $apartmentRequired;
		return $this;
	}
	
	/**
	 *
	 * @return bool
	 */
	public function getIsCommercial() {
		return $this->_isCommercial;
	}
	
	/**
	 *
	 * @param bool $isCommercial
	 * @return $this
	 */
	public function setIsCommercial($isCommercial) {
		$this->_isCommercial = $isCommercial;
		return $this;
	}
	
	/**
	 * Get address string suitable for list item
	 *
	 * @return string
	 */
	public function getAddressString() {
		$street = $this->getStreet ();
		$streets = trim ( implode ( ', ', $street ) );
		$city = $this->getCity ();
		$region = $this->getRegion ();
		$regionId = $this->getRegionId ();
		$postcode = $this->getPostcode ();
		$countryId = $this->getCountryId ();
		
		$addressAr = [ 
				$streets,
				$city,
				$region,
				$postcode,
				$countryId 
		];
		
		$addressString = trim ( implode ( ', ', $addressAr ) );
		
		if ($this->_showService){
			$addressString .= ' ('.__($this->_serviceLabel).')';
		}
		
		return $addressString;
	}
}