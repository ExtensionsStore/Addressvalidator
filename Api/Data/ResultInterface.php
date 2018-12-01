<?php

/**
 * Result interface
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Api\Data;
use Magento\Framework\Api\ExtensibleDataInterface;


interface ResultInterface extends ExtensibleDataInterface {
	
	/**
	 *
	 * @return bool
	 */
	public function getError() ;
	
	/**
	 *
	 * @param bool $error
	 * @return $this
	 */
	public function setError($error) ;
	
	/**
	 *
	 * @return string
	 */
	public function getErrorMessage() ;
	
	/**
	 *
	 * @param string $errorMessage
	 * @return $this
	 */
	public function setErrorMessage($errorMessage) ;
	
	/**
	 *
	 * @return string
	 */
	public function getService() ;
	
	/**
	 *
	 * @param string $service
	 * @return $this
	 */
	public function setService($service) ;
	
	/**
	 *
	 * @return string
	 */
	public function getServiceLabel() ;
	
	/**
	 *
	 * @param string $serviceLabel
	 * @return $this
	 */
	public function setServiceLabel($serviceLabel) ;
	
	/**
	 *
	 * @return bool
	 */
	public function getShowService() ;
	
	/**
	 *
	 * @param bool $showService
	 * @return $this
	 */
	public function setShowService($showService) ;
	
	/**
	 *
	 * @return bool
	 */
	public function getApartmentRequired() ;
	
	/**
	 *
	 * @param bool $apartmentRequired
	 * @return $this
	 */
	public function setApartmentRequired($apartmentRequired) ;
	
	/**
	 *
	 * @return bool
	 */
	public function getIsCommercial() ;
	
	/**
	 *
	 * @param bool $isCommercial
	 * @return $this
	 */
	public function setIsCommercial($isCommercial) ;
	
}