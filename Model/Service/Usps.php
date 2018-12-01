<?php

/**
 * Usps service
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model\Service;

class Usps extends AbstractService {

	const SERVICE_CODE = 'usps';
	const SERVICE_LABEL = 'United States Postal Service';
	protected $_url = 'http://production.shippingapis.com/ShippingAPI.dll';
	protected $_urlSecure = 'https://secure.shippingapis.com/ShippingAPI.dll';
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \ExtensionsStore\Addressvalidator\Model\Service\AbstractService::getResults()
	 */
	public function getResults(){
		$results = [];
		$url = $this->getUrl();
		$userId = $this->_scopeConfig->getValue('carriers/usps/userid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$streets = $this->getStreet();
		$regionModel = $this->_directoryRegionFactory->create()->load($this->getRegion(),'default_name');
		$regionModel->loadByName($this->getRegion(), $this->getCountryId());
		$state = $regionModel->getCode();
		$zip5 = $this->getPostcode();
		$zip4 = '';
		if (strlen($zip5)>5){
			$zipPlus4 = explode('-',$zip5);
			if (is_array($zipPlus4) && count($zipPlus4)==2){
				$zip5 = $zipPlus4[0];
				$zip4 = $zipPlus4[1];
			}
		}
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<AddressValidateRequest USERID="'.$userId.'">
  <Address>
    <FirmName><![CDATA['.$this->getCompany().']]></FirmName>
    <Address1><![CDATA['.@$streets[1].']]></Address1>
    <Address2><![CDATA['.$streets[0].']]></Address2>
    <City><![CDATA['.$this->getCity().']]></City>
    <State><![CDATA['.$state.']]></State>
    <Zip5><![CDATA['.$zip5.']]></Zip5>
    <Zip4><![CDATA['.$zip4.']]></Zip4>
  </Address>
</AddressValidateRequest>';
		
		$fields = array('API' => 'Verify', 'XML' => $xml);
		$message = http_build_query($fields, null, '&');
		
		$client = $this->_httpClientFactory->create();
		$client->setUri($url);
		$client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
		$client->setMethod(\Zend_Http_Client::POST);
		$client->setParameterPost($fields);
		$response = $client->request()->getBody();
				
		$responseXml = simplexml_load_string($response);
		
		if (isset($responseXml->Address)){
			if (!isset($responseXml->Address->Error)){
				
				if (!isset($responseXml->Address->ReturnText)){
					foreach ($responseXml->Address as $address){
						
						$result= $this->createResult();
						$street = [(string)$address->Address2, (string)$address->Address1];
						$city = (string)$address->City;
						$state = (string)$address->State;
						$countryId = $this->getCountryId();
						$regionModel = $this->_directoryRegionFactory->create();
						$regionModel->loadByCode($state, $countryId);
						$regionId = $regionModel->getId();
						$region = $regionModel->getCode();
						$postcode = (string)$address->Zip5.'-'.(string)$address->Zip4;
						
						$data = [
								'street' => $street,
								'city' => $city,
								'region_id' => $regionId,
								'region' => $region,
								'postcode' => $postcode,
								'country_id' => $countryId,
						];
						
						$result->setData($data);
						$results[] = $result;
					}
				} else {
					$result= $this->createResult();
					$result->setError(true);
					$errorMessage = (string)$responseXml->Address->ReturnText;
					
					if (strpos($errorMessage,'apartment') !== false){
						$errorMessage = ($this->_requireApartment && !isset($streets[1])) ? $this->getApartmentRequiredMessage() : $this->getApartmentNotFoundMessage();
					}
					$errorMessage .= ' ('.__(self::SERVICE_LABEL).')';
					$result->setErrorMessage($errorMessage);
					$results[] = $result;
				}
				
			} else {
				$result= $this->createResult();
				$result->setError(true);
				$errorMessage = (string)$responseXml->Address->Error->Description;
				$result->setErrorMessage($errorMessage);
				$results[] = $result;
			}
		}
		
		return $results;
	}
}