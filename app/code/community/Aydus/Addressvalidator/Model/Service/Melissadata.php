<?php

/** 
 * Melissa Data service
 * 
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_Addressvalidator_Model_Service_Melissadata extends Aydus_Addressvalidator_Model_Service_Abstract
{
	/**
	 * Service 
	 * @var string $_service
	 * @var string $_url
	 */
	protected $_service = 'melissadata';
	protected $_url = 'http://address.melissadata.net/V3/SOAP/GlobalAddress';
		
	public function _construct()
	{
		parent::_construct();
		
		$this->_headers = array(
			'SOAPAction: "urn:mdGlobalAddress/AddressCheckSoap/doGlobalAddress"',
			'User-Agent: PHP-SOAP/'.phpversion(),
			'Connection: Keep-Alive',
		);
	}
	
	/**
	 * Get soap request message
	 * 
	 * @param Mage_Customer_Model_Address $customerAddress
	 * @return string
	 */
	protected function _getMessage($customerAddress)
	{
		$customerId = Mage::getStoreConfig('aydus_addressvalidator/melissadata/customer_id');
				
		$extractableArray = $this->_getExtractableAddressArray($customerAddress);
		extract($extractableArray);
		
		$message =
'<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
	xmlns:ns1="urn:mdGlobalAddress">
	<SOAP-ENV:Body>
		<ns1:doGlobalAddress>
			<ns1:Request>
				<ns1:TransmissionReference></ns1:TransmissionReference>
				<ns1:CustomerID>'.$customerId.'</ns1:CustomerID>
				<ns1:Options>LineSeparator:Semicolon,OutputScript:NoChange,CountryOfOrigin:United
					States</ns1:Options>
				<ns1:Records>
					<ns1:RequestRecord>
						<ns1:RecordID>1</ns1:RecordID>
						<ns1:Organization><![CDATA['.$company.']]></ns1:Organization>
						<ns1:AddressLine1><![CDATA['.$street1.']]></ns1:AddressLine1>
						<ns1:AddressLine2><![CDATA['.$street2.']]></ns1:AddressLine2>
						<ns1:Locality><![CDATA['.$city.']]></ns1:Locality>
						<ns1:AdministrativeArea><![CDATA['.$region.']]></ns1:AdministrativeArea>
						<ns1:PostalCode><![CDATA['.$postcode.']]></ns1:PostalCode>
						<ns1:Country><![CDATA['.$countryId.']]></ns1:Country>
					</ns1:RequestRecord>
				</ns1:Records>
			</ns1:Request>
		</ns1:doGlobalAddress>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
		
		return $message;
	}
	
	
	/**
	 * Process response string into json object and extract addresses array
	 * 
	 * @param string $response
	 * @return array
	 */
	protected function _processResponse($response)
	{
		$return = array();
		$return['error'] = true;
				
		$responseJson = Mage::helper('addressvalidator')->xmlToObject($response);
		
		$totalRecords = $responseJson->sBody->doGlobalAddressResponse->doGlobalAddressResult->TotalRecords;
			
		if ($totalRecords > 0) {
			
			$return['error'] = false;
			$return['data'] = (array)$responseJson->sBody->doGlobalAddressResponse->doGlobalAddressResult->Records;
				
		}	
		
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
		
		$regionModel = Mage::getModel ('directory/region');
		
		foreach ($responseData as $i=>$responseRecord) {
			
			if ($i + 1 > $this->_numResults){
				
				break;
			}
			
			$country = $responseRecord->CountryName;
			$street = array($responseRecord->AddressLine1, $responseRecord->AddressLine2);
			$city = $responseRecord->Locality;
			$province = $responseRecord->AdministrativeArea;
			$regionModel->loadByName( $province, $this->_country->getId() );
			$region = ($regionModel && $regionModel->getId()) ? $regionModel->getName() : $province;
			if ($responseRecord->AddressLine2 == $region){
				unset($street[1]);
			}
			
			$regionId = ($regionModel && $regionModel->getId()) ? $regionModel->getCode() : null;
			
			$postcode = $responseRecord->PostalCode;
			
			$results[] = array(
				'country_id' => $this->_country->getId(),
				'country' => $country,
				'street' => $street,
				'city' => $city,
				'region' => $region,
				'region_id' => $regionId,
				'postcode' => $postcode,
			);
		}
		
		return $results;
	}
	
}