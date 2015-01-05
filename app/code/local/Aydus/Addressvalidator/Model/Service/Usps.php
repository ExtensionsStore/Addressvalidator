<?php

/** 
 * Usps service
 *
 * @category   Aydus
 * @package	   Aydus_Addressvalidator
 * @author     Aydus Consulting <davidt@aydus.com>
 */

class Aydus_Addressvalidator_Model_Service_Usps extends Aydus_Addressvalidator_Model_Service_Abstract
{
	/**
	 * Service 
	 * @var string $_service
	 * @var string $_url
	 */
	protected $_service = 'usps';
	protected $_url = '';
	
	public function _construct()
	{
		parent::_construct();
		
	}
	
	/**
	 * Get soap request message
	 * 
	 * @param Mage_Customer_Model_Address $customerAddress
	 * @return string
	 */
	protected function _getMessage($customerAddress)
	{
		
		$extractableArray = $this->_getExtractableAddressArray($customerAddress);
		extract($extractableArray);
				
		$message = '<?xml	version="1.0"	encoding="utf-8"? />';	

		return $message;
	}
	
	protected function _getResponse($message)
	{
		$hash = md5($message);
		$this->_hash = $hash;

		return '<xml />';
		
	}	
		
	/**
	 * Process response string into json object and extract addresses array
	 * 
	 * @param string $response
	 * @return array
	 */
	protected function _processResponse($response)
	{
		$return = array('error' => false, 'data'=> array(1,2));

		return $return;
	}
	
	/**
	 * Process the result status
	 * 
	 * @param string $processStatus
	 * @return int 0, 1, 2 (internal error, incorrect address, verified)
	 */
	protected function _processStatus($processStatus)
	{
		$return = 0;

		return $return;
	}
	
	
	/**
	 * Generate array of addresses
	 * 
	 * @param array $responseData
	 * @return array
	 */
	protected function _processResults(array $responseData)
	{
		$results = array();
		
		$results[] = array(
				'country_id' => 'US',
				'country' => 'US',
				'street' => '58 Stone Hollow Drive',
				'city' => 'Sicklerville',
				'region' => 'New Jersey',
				'region_id' => '41',
				'postcode' => '08081',
		);		
		
		return $results;
	}
	
}