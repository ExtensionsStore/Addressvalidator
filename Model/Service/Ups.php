<?php

/**
 * Ups service
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model\Service;

class Ups extends AbstractService {
	
	const SERVICE_CODE = 'ups';
	const SERVICE_LABEL = 'United Parcel Service';
	protected $_url = 'https://onlinetools.ups.com/rest/XAV';
	protected $_urlSecure = 'https://onlinetools.ups.com/rest/XAV';
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \ExtensionsStore\Addressvalidator\Model\Service\AbstractService::getResults()
	 */
	public function getResults(){
		
		$results = [];
		
		try {
			$username = $this->_scopeConfig->getValue('carriers/ups/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$password = $this->_scopeConfig->getValue('carriers/ups/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$licenseNumber = $this->_scopeConfig->getValue('carriers/ups/access_license_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			
			$streets = $this->getStreet();
			$addressLine = trim(implode(', ', $streets));//@todo UPS doesn't seem to have line2
			$city = $this->getCity();
			$regionModel = $this->_directoryRegionFactory->create()->load($this->getRegion(),'default_name');
			$regionModel->loadByName($this->getRegion(), $this->getCountryId());
			$state = $regionModel->getCode();
			$postcode = $this->getPostcode();
			$countryId = $this->getCountryId();
			
			$requestData = [
					"UPSSecurity" => [
							"UsernameToken" => [
									"Username" => $username,
									"Password" => $password
							],
							"ServiceAccessToken" => [
									"AccessLicenseNumber" => $licenseNumber
							]
					],
					"XAVRequest" => [
							"Request" => [
									"RequestOption" => "1",
									"TransactionReference" => [
											"CustomerContext" => "Your Customer Context"
									]
							],
							"MaximumListSize" => "10",
							"AddressKeyFormat" => [
									"AddressLine" => $addressLine,
									"PoliticalDivision2" => $city,
									"PoliticalDivision1" => $state,
									"PostcodePrimaryLow" => $postcode,
									"CountryCode" => 'PR',//$countryId
							]
					]
			];
			
			$url = $this->getUrl();
			$client = $this->_httpClientFactory->create();
			$client->setUri($url);
			$client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
			$client->setMethod(\Zend_Http_Client::POST);
			$rawData = $this->_jsonHelper->jsonEncode($requestData);
			$client->setRawData($rawData, null);
			$response = $client->request();
			$body = $response->getBody();
			
			$responseObj = json_decode($body, false);
			if ($responseObj && isset($responseObj->XAVResponse)){
				if ($responseObj->XAVResponse->Response->ResponseStatus->Code == 1) {
					
					$candidates = $responseObj->XAVResponse->Candidate;
					if (count($candidates)==1){
						$candidates = [$candidates];
					} 
					
					foreach ($candidates as $candidate){
						
						$address = $candidate->AddressKeyFormat;
						
						$result= $this->createResult();
						$street = (!is_array($address->AddressLine)) ? [$address->AddressLine] : $address->AddressLine;
						$city = $address->PoliticalDivision2;
						$state = $address->PoliticalDivision1;
						$countryId = $address->CountryCode;
						$regionModel = $this->_directoryRegionFactory->create();
						$regionModel->loadByCode($state, $countryId);
						$regionId = $regionModel->getId();
						$region = $regionModel->getCode();
						$postcode = $address->PostcodePrimaryLow.'-'.$address->PostcodeExtendedLow;
						
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
					
					$error = @$responseJson->Response->Error;
					$errorMessage = 'An error occurred during service call to UPS.';
					//@todo has this changed?
					if ($error && $error->ErrorCode && $error->ErrorDescription){
						$errorMessage = $error->ErrorCode.'-'.$error->ErrorDescription;
					}
					
					$result= $this->_resultFactory->create();
					$result->setError(true);
					$result->setErrorMessage($errorMessage);
					$results[] = $result;
				}
			} else {
				
				if (isset($responseObj->Fault)){
					$this->_logger->log ( \Monolog\Logger::ERROR, $responseObj->Fault->faultstring );
				}
				
			}
		}catch(\Exception $e){
			$this->_logger->log ( \Monolog\Logger::ERROR, $e->getMessage () );
		}
		
		return $results;
		
	}
}