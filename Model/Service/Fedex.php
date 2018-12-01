<?php

/**
 * FedEx service
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model\Service;

use Magento\Framework\Module\Dir;

class Fedex extends AbstractService {
	
	const SERVICE_CODE = 'fedex';
	const SERVICE_LABEL = 'FedEx';
	protected $_url = 'https://gateway.fedex.com:443/web-services';
	protected $_urlSecure = 'https://gateway.fedex.com:443/web-services';
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \ExtensionsStore\Addressvalidator\Model\Service\AbstractService::getResults()
	 */
	public function getResults(){
		
		$results = [];
		
		try {
			$account = $this->_scopeConfig->getValue('carriers/fedex/account', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$meter = $this->_scopeConfig->getValue('carriers/fedex/meter_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$key = $this->_scopeConfig->getValue('carriers/fedex/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$password = $this->_scopeConfig->getValue('carriers/fedex/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$requestTimeStamp = date('c');
			
			$company = $this->getCompany();
			$streets = $this->getStreet();
			$city = $this->getCity();
			$regionModel = $this->_directoryRegionFactory->create()->load($this->getRegion(),'default_name');
			$regionModel->loadByName($this->getRegion(), $this->getCountryId());
			$state = $regionModel->getCode();
			$postcode = $this->getPostcode();
			$countryId = $this->getCountryId();
			
			$wsdl = $this->_configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'ExtensionsStore_Addressvalidator') . '/wsdl/AddressValidationService_v4.wsdl';
			
			$client = $this->_soapClientFactory->create($wsdl);
			
			$requestData = [
					'WebAuthenticationDetail' => [
							'UserCredential' => [
									'Key' => $key,
									'Password' => $password,
							]
					],
					'ClientDetail' => [
							'AccountNumber' => $account,
							'MeterNumber' => $meter,
					],
					'Version' => [
							'ServiceId' => 'aval',
							'Major' => 4,
							'Intermediate'=>0,
							'Minor' => 0,
					],
					'RequestTimestamp' => $requestTimeStamp,
					'Options' => [
							'VerifyAddresses' => true,
							'CheckResidentialStatus' => true,
							'DirectionalAccuracy'=> 'MEDIUM',
							'ConvertToUpperCase' => true,
					],
					'AddressesToValidate' => [
							'AddressId' => 1,
							'CompanyName' => $company,
							'Address' => [
									'StreetLines' => $streets,
									'City' => $city,
									'StateOrProvinceCode' => $state,
									'PostalCode' => $postcode,
									'CountryCode' => $countryId,
							]
					]
			];
			
			$responseObj = $client->addressValidation($requestData);
			
			if ($responseObj && isset($responseObj->AddressResults)){
				$addressResults = $responseObj->AddressResults;
				
				$classification = $addressResults->Classification;
				$invalidAddress = ($classification == 'UNKNOWN') ? true : false;
				$isCommercial = ($classification == 'BUSINESS' || $classification == 'MIXED') ? true : false;
				$attributes = $addressResults->Attributes;
				$apartmentRequired = false;
				$apartmentMissing = false;
				$apartmentInvalid = false;
				$streetValidated = !$invalidAddress;
				$cityStateValidated = !$invalidAddress;
				$postalValidated = !$invalidAddress;
				
				foreach ($attributes as $attribute){
					if ($attribute->Name == 'SuiteRequiredButMissing' && $attribute->Value == 'true'){
						$apartmentRequired = true;
						$apartmentMissing = true;
					}
					if ($attribute->Name == 'InvalidSuiteNumber' && $attribute->Value == 'true'){
						$apartmentRequired = true;
						$apartmentInvalid = true;
					}
					if ($attribute->Name == 'StreetValidated' && $attribute->Value == 'true'){
						$streetValidated = true;
					}
					if ($attribute->Name == 'CityStateValidated' && $attribute->Value == 'true'){
						$cityStateValidated = true;
					}
					if ($attribute->Name == 'PostalValidated' && $attribute->Value == 'true'){
						$postalValidated = true;
					}
				}
				$result= $this->createResult();
				$result->setIsCommercial($isCommercial);
				if (!$streetValidated || !$cityStateValidated || !$postalValidated){
					$result->setError(true);
					$errorMessage = '';
					if (!$streetValidated && !$cityStateValidated  && !$postalValidated){
						$errorMessage = __('Street address, city or state and postcode are invalid.');
					}else if (!$streetValidated && !$cityStateValidated){
						$errorMessage = __('Street address and city/state are invalid.');
					}else if (!$streetValidated && !$postalValidated){
						$errorMessage = __('Street address and postcode are invalid.');
					}else if (!$streetValidated){
						$errorMessage = __('Street address is invalid.');
					} else if (!$cityStateValidated  && !$postalValidated){
						$errorMessage = __('City or state and postcode are invalid.');
					} else if (!$cityStateValidated){
						$errorMessage = __('City or state is invalid.');
					} else if (!$postalValidated){
						$errorMessage = __('Postcode is invalid.');
					}

					$errorMessage .= ' ('.__(self::SERVICE_LABEL).')';
					$result->setErrorMessage($errorMessage);
				}
				$result->setApartmentRequired($apartmentRequired);
				if ($apartmentMissing || $apartmentInvalid){
					$result->setApartmentRequired(true);
					$result->setError(true);
					$errorMessage = ($apartmentMissing) ? $this->getApartmentRequiredMessage() : $this->getApartmentNotFoundMessage();
					$errorMessage .= ' ('.__(self::SERVICE_LABEL).')';
					$result->setErrorMessage($errorMessage);
				}
				if (isset($addressResults->EffectiveAddress)){
					$effectiveAddress = $addressResults->EffectiveAddress;
					$street = (array)@$effectiveAddress->StreetLines;
					$city = @$effectiveAddress->City;
					$state = @$effectiveAddress->StateOrProvinceCode;
					$regionModel = $this->_directoryRegionFactory->create();
					$regionModel->loadByCode($state, $countryId);
					$regionId = $regionModel->getId();
					$region = $regionModel->getCode();
					$postcode = @$effectiveAddress->PostalCode;
					$countryId = @$effectiveAddress->CountryCode;
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