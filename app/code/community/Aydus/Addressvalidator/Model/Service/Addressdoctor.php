<?php

/**
 * Address Doctor service
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */
class Aydus_Addressvalidator_Model_Service_Addressdoctor extends Aydus_Addressvalidator_Model_Service_Abstract {

    /**
     * Service 
     * @var string $_service
     * @var string $_url
     */
    protected $_service = 'addressdoctor';
    protected $_url = 'http://validator5.addressdoctor.com/Webservice5/v2/AddressValidation.asmx';
    protected $_validStatuses = array(
        "JO" => array('V4', 'V3', 'V2', 'C4', 'C3', 'C2', 'I4', 'I3'),
        "NZ" => array('V4', 'V3', 'V2', 'C4', 'C3', 'I4', 'I3'),
        "SA" => array('V4', 'V3', 'V2', 'C4', 'C3', 'C2', 'I4', 'I3'),
        "AE" => array('V4', 'V3', 'V2', 'C4', 'C3', 'C2', 'I4', 'I3'),
    );

    public function _construct() {
        parent::_construct();

        $cache = Mage::app()->getCache();
        $cacheKey = md5(get_class($this) . "_valid_exceptions");
        $validStatuses = $cache->load($cacheKey);

        $validStatuses = unserialize($validStatuses);

        if (!is_array($validStatuses) || count($validStatuses) == 0) {

            $validExceptions = Mage::getStoreConfig('aydus_addressvalidator/addressdoctor/valid_exceptions');

            if ($validExceptions) {

                $validExceptions = unserialize($validExceptions);

                if (is_array($validExceptions) && count($validExceptions) > 0) {

                    $country = Mage::getModel('directory/country');

                    $validStatuses = array();

                    foreach ($validExceptions as $i => $validException) {

                        $iso3Code = $validException['iso3_code'];
                        $filterValids = $validException;
                        unset($filterValids['country_name']);
                        unset($filterValids['iso3_code']);
                        $filteredValids = array_diff($filterValids, array(0));

                        $country->loadByCode($iso3Code);
                        $valids = array_keys($filteredValids);
                        $validStatuses[$country->getId()] = $valids;
                    }

                    $this->_validStatuses = $validStatuses;

                    $tags = array(); //@todo
                    $cache->save(serialize($validStatuses), $cacheKey, $tags, 604800);
                }
            }
        } else {

            $this->_validStatuses = $validStatuses;
        }
    }

    /**
     * Get soap request message
     * 
     * @param Mage_Customer_Model_Address $customerAddress
     * @return string
     */
    protected function _getMessage($customerAddress) {
        $customerId = Mage::getStoreConfig('aydus_addressvalidator/addressdoctor/customer_id');
        $password = Mage::helper('core')->decrypt(Mage::getStoreConfig('aydus_addressvalidator/addressdoctor/password'));
        $testMode = (int) Mage::getStoreConfig('aydus_addressvalidator/addressdoctor/test_mode');
        $test = ($testMode) ? 'TEST' : '';

        $extractableArray = $this->_getExtractableAddressArray($customerAddress);
        extract($extractableArray);

        $message = '<?xml	version="1.0"	encoding="utf-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="http://validator5.AddressDoctor.com/Webservice5/v2">
   <soapenv:Header/>
   <soapenv:Body>
      <v1:Process>
         <!--Optional:-->
         <v1:login>' . $customerId . '</v1:login>
         <!--Optional:-->
         <v1:password>' . $password . '</v1:password>
         <!--Optional:-->
         <v1:parameters>
            <!--Optional:-->
            <v1:ProcessMode>INTERACTIVE</v1:ProcessMode>
            <!--Optional:-->
            <v1:ServiceParameters>
               <!--Optional:-->
               <v1:JobToken></v1:JobToken>
               <!--Optional:-->
               <v1:CampaignId>Aydus Cosmetics</v1:CampaignId>
               <!--Optional:-->
               <v1:ReservedXml></v1:ReservedXml>
               <!--Optional:-->
               <v1:UseTransactionPool>' . $test . '</v1:UseTransactionPool>
            </v1:ServiceParameters>
            <!--Optional:-->
            <v1:ValidationParameters>
               <!--Optional:-->
               <v1:FormatType></v1:FormatType>
               <!--Optional:-->
               <v1:FormatDelimiter></v1:FormatDelimiter>
               <!--Optional:-->
               <v1:DefaultCountryISO3></v1:DefaultCountryISO3>
               <!--Optional:-->
               <v1:ForceCountryISO3></v1:ForceCountryISO3>
               <!--Optional:-->
               <v1:CountryType></v1:CountryType>
               <!--Optional:-->
               <v1:CountryOfOriginISO3></v1:CountryOfOriginISO3>
               <v1:StreetWithNumber>false</v1:StreetWithNumber>
               <v1:FormatWithCountry>false</v1:FormatWithCountry>
               <v1:ElementAbbreviation>true</v1:ElementAbbreviation>
               <!--Optional:-->
               <v1:PreferredScript>ASCII_EXTENDED</v1:PreferredScript>
               <!--Optional:-->
               <v1:PreferredLanguage>ENGLISH</v1:PreferredLanguage>
               <!--Optional:-->
               <v1:AliasStreet></v1:AliasStreet>
               <!--Optional:-->
               <v1:GlobalCasing></v1:GlobalCasing>
               <v1:GlobalMaxLength>0</v1:GlobalMaxLength>
               <!--Optional:-->
               <v1:MatchingScope></v1:MatchingScope>
               <v1:MaxResultCount>0</v1:MaxResultCount>
               <!--Optional:-->
               <v1:DualAddressPriority></v1:DualAddressPriority>
               <v1:StandardizeInvalidAddresses>true</v1:StandardizeInvalidAddresses>
               <!--Optional:-->
               <v1:RangesToExpand>ONLY_WITH_VALID_ITEMS</v1:RangesToExpand>
               <v1:FlexibleRangeExpansion>false</v1:FlexibleRangeExpansion>
               <!--Zero or more repetitions:-->
               <v1:Standardizations>
                  <!--Optional:-->
                  <v1:Element>DeliveryAddressLine</v1:Element>
                  <!--Optional:-->
                  <v1:Casing>NOCHANGE</v1:Casing>
                  <v1:MaxLength>128</v1:MaxLength>
                  <v1:MaxItemCount>3</v1:MaxItemCount>
               </v1:Standardizations>
               <!--Zero or more repetitions:-->
               <v1:AdditionalInformationSet>
                  <!--Optional:-->
                  <v1:Name></v1:Name>
                  <!--Optional:-->
                  <v1:Value></v1:Value>
               </v1:AdditionalInformationSet>
            </v1:ValidationParameters>
         </v1:parameters>
         <!--Optional:-->
         <v1:addresses>
            <!--Zero or more repetitions:-->
            <v1:Address>
               <!--Optional:-->
               <v1:RecordId></v1:RecordId>
               <!--Optional:-->
               <v1:Organization>
                  <!--Zero or more repetitions:-->
                  <v1:string><![CDATA[' . $company . ']]></v1:string>
               </v1:Organization>
               <!--Optional:-->
               <v1:Department>
                  <!--Zero or more repetitions:-->
                  <v1:string></v1:string>
               </v1:Department>
               <!--Optional:-->
               <v1:Contact>
                  <!--Zero or more repetitions:-->
                  <v1:string><![CDATA[' . $name . ']]></v1:string>
               </v1:Contact>
               <!--Optional:-->
               <v1:Email>
                  <!--Zero or more repetitions:-->
                  <v1:string><![CDATA[' . $email . ']]></v1:string>
               </v1:Email>
               <!--Optional:-->
               <v1:Street>
                  <!--Zero or more repetitions:-->
                  <v1:string><![CDATA[' . $street1 . ']]></v1:string>
                  <v1:string><![CDATA[' . $street2 . ']]></v1:string>
               </v1:Street>
               <!--Optional:-->
               <v1:HouseNumber>
                  <!--Zero or more repetitions:-->
                  <v1:string></v1:string>
               </v1:HouseNumber>
               <v1:Locality>
                  <!--Zero or more repetitions:-->
                  <v1:string><![CDATA[' . $city . ']]></v1:string>
               </v1:Locality>
			   <v1:Province>
                  <!--Zero or more repetitions:-->
                  <v1:string><![CDATA[' . $region . ']]></v1:string>
               </v1:Province>                  		
               <v1:PostalCode>
                  <!--Zero or more repetitions:-->
                  <v1:string><![CDATA[' . $postcode . ']]></v1:string>
               </v1:PostalCode>
               <!--Optional:-->
               <v1:Country>
                  <!--Zero or more repetitions:-->
                  <v1:string><![CDATA[' . $countryId . ']]></v1:string>
               </v1:Country>
            </v1:Address>
         </v1:addresses>
      </v1:Process>
   </soapenv:Body>
</soapenv:Envelope>';

        return $message;
    }

    /**
     * Process response string into json object and extract addresses array
     * 
     * @param string $response
     * @return array
     */
    protected function _processResponse($response) {
        $return = array();
        $return['error'] = true;

        $responseJson = Mage::helper('addressvalidator')->xmlToObject($response);

        $processResult = $responseJson->soapBody->ProcessResponse->ProcessResult;
        $statusCode = $processResult->StatusCode;
        $statusMessage = $processResult->StatusMessage;

        if ($statusCode == 100) {

            $results = $processResult->Results;
            $result = $results->Result;

            $processStatus = $result->ProcessStatus;
            $return['response_code'] = $processStatus;
            $processResult = $this->_processStatus($processStatus);

            $message = $this->_hash . '-' . $processStatus . '-' . $processResult;

            if ($processResult > 0) {

                $return['error'] = false;

                $countryId = $this->_country->getCountryId();
                $validStatusKeys = array_keys($this->_validStatuses);
                $validStatuses = (isset($this->_validStatuses[$countryId])) ? $this->_validStatuses[$countryId] : array();
                $exception = (in_array($countryId, $validStatusKeys) && in_array($processStatus, $validStatuses)) ? true : false;

                if ($processResult == 2 || ($processResult == 1 && $exception)) {

                    $resultDataSet = $result->ResultDataSet;
                    $return['data'] = (is_array($resultDataSet->ResultData)) ? $resultDataSet->ResultData : array($resultDataSet->ResultData);
                } else {

                    $return['data'] = array();
                }
            } else {
                //internal error
                Mage::log($message, null, 'aydus_addressvalidator.log');
            }
        } else {

            Mage::log($statusCode . '-' . $statusMessage, null, 'aydus_addressvalidator.log');
        }

        return $return;
    }

    /**
     * Process the result status
     * 
     * @param string $processStatus
     * @return int 0, 1, 2 (internal error, incorrect address, verified)
     */
    protected function _processStatus($processStatus) {
        $return = 0;
        $validCodes = Mage::getStoreConfig('aydus_addressvalidator/addressdoctor/valid_codes');
        if ($validCodes) {

            $validCodes = explode(',', $validCodes);

            if (is_array($validCodes) && in_array($processStatus, $validCodes)) {
                return 2;
            }
        }

        $status = substr($processStatus, 0, 1);
        $grade = (int) substr($processStatus, 1, 1);

        switch ($status) {

            //verified
            case 'V' :

                switch ($grade) {

                    //V4 - Input data correct - all (postally relevant) elements were checked and input matched perfectly
                    //V3 - Input data correct on input but some or all elements were standardized or input contains outdated names or exonyms
                    case 4 : case 3 :
                        $return = 2;
                        break;
                    //V2 - Input data correct but some elements could not be verified because of incomplete reference data
                    //V1 - Input data correct but the user standardization has deteriorated deliverability (wrong element user standardization � for example, postcode length chosen is too short). Not set by validation
                    case 2 : case 1 :
                        $return = 1;
                        break;
                    default :
                        break;
                }
                break;

            //corrected					
            case 'C' :

                switch ($grade) {

                    //C4 - All (postally relevant) elements have been checked
                    //C3 - 3 Some elements could not be checked
                    case 4 : case 3 :
                        $return = 2;
                        break;
                    //C2 - Delivery status unclear (lack of reference data) 
                    //C1 -  Delivery status unclear because user standardization was wrong. Not set by validation
                    case 2 : case 1 :
                        $return = 1;
                        break;
                    default :
                        break;
                }
                break;

            //fast completion
            case 'Q' :

                switch ($grade) {

                    //Q3 - Suggestions are available � complete address 
                    case 3 :
                        $return = 2;
                        break;
                    //Q2 - Suggested address is complete but combined with elements from the input (added or deleted) 
                    //Q1 - Suggested address is not complete (enter more information) 
                    //Q0 - Insufficient information provided to generate suggestions
                    case 2 : case 1 : case 0 :
                        $return = 1;
                        break;
                    default :
                        break;
                }
                break;

            //incorrect
            case 'I' :

                switch ($grade) {

                    //I4 - Data could not be corrected completely, but is very likely to be deliverable � single match (e.g. HNO is wrong but only 1 HNO is found in reference data)
                    //I3 - Data could not be corrected completely, but is very likely to be deliverable � multiple matches (e.g. HNO is wrong but more than 1 HNO is found in reference data) 
                    case 4 : case 3 :
                        $return = 2;
                        break;
                    //I2 - Data could not be corrected, but there is a slim chance that the address is deliverable
                    //I1 - Data could not be corrected and is pretty unlikely to be delivered 
                    case 2 : case 1 :
                        $return = 1;
                        break;
                    default :
                        break;
                }
                break;

            //not processed, webservice error 
            case 'N' : case 'W' : default :

                break;
        }

        return $return;
    }

    /**
     * Generate array of addresses
     * 
     * @param array $responseData
     * @return array
     */
    protected function _processResults(array $responseData) {
        $results = array();

        $regionModel = Mage::getModel('directory/region');

        foreach ($responseData as $i => $resultAddress) {

            if ($i + 1 > $this->_numResults) {

                break;
            }

            try {

                $addressData = $resultAddress->Address;
                $country = ucwords(strtolower($addressData->Country->string));
                //result country may be different from request country
                $countries = Mage::app()->getLocale()->getCountryTranslationList();
                $countryId = false;
                foreach ($countries as $countryCode => $countryName) {
                    if ($country == $countryName) {
                        $countryId = $countryCode;
                        break;
                    }
                }
                if (!$countryId) {
                    $countryId = $this->_country->getId();
                }

                $street = (array) $addressData->DeliveryAddressLines->string;
                $city = (array) $addressData->Locality->string;

                $regionId = '';
                $region = array('');

                if (isset($addressData->Province)) {
                    $province = (array) $addressData->Province->string;
                    $regionModel->loadByName($province[0], $countryId);
                    $regionModel = (!$regionModel->getId()) ? $regionModel->loadByCode($province[0], $countryId) : $regionModel;
                    $region = ($regionModel->getId()) ? array($regionModel->getName()) : $province;
                    $regionId = ($regionModel->getId()) ? $regionModel->getId() : null;
                }

                $postcode = $addressData->PostalCode->string;

                $results[] = array(
                    'country_id' => $countryId,
                    'country' => $country,
                    'street' => $street,
                    'city' => $city,
                    'region' => $region[0],
                    'region_id' => $regionId,
                    'postcode' => $postcode,
                );
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'aydus_addressvalidator.log');
            }
        }

        return $results;
    }

}
