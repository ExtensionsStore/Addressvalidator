<?php

/**
 * Fedex service
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */
class Aydus_Addressvalidator_Model_Service_Fedex extends Aydus_Addressvalidator_Model_Service_Abstract {

    /**
     * Service 
     * @var string $_service
     * @var string $_url
     */
    protected $_service = 'fedex';
    protected $_url = 'https://gateway.fedex.com:443/web-services';

    public function _construct() {
        parent::_construct();

    }

    /**
     * Get soap request message
     * 
     * @param Mage_Customer_Model_Address $customerAddress
     * @return string
     */
    protected function _getMessage($customerAddress) {
        
        $account = Mage::getStoreConfig('carriers/fedex/account');
        $meter = Mage::getStoreConfig('carriers/fedex/meter_number');
        $key = Mage::getStoreConfig('carriers/fedex/key');
        $password = Mage::getStoreConfig('carriers/fedex/password');

        $extractableArray = $this->_getExtractableAddressArray($customerAddress);
        extract($extractableArray);
        
        $regionModel = Mage::getModel('directory/region')->load($region,'default_name');
        $state = $regionModel->getCode();
        
        $requestTimeStamp = date('c');

        $message = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://fedex.com/ws/addressvalidation/v2">
<SOAP-ENV:Body>
    <ns1:AddressValidationRequest>
        <ns1:WebAuthenticationDetail>
            <ns1:UserCredential>
                <ns1:Key>'.$key.'</ns1:Key>
                <ns1:Password>'.$password.'</ns1:Password>
            </ns1:UserCredential>
        </ns1:WebAuthenticationDetail>
        <ns1:ClientDetail>
            <ns1:AccountNumber>'.$account.'</ns1:AccountNumber>
            <ns1:MeterNumber>'.$meter.'</ns1:MeterNumber>
        </ns1:ClientDetail>
        <ns1:Version>
            <ns1:ServiceId>aval</ns1:ServiceId>
            <ns1:Major>2</ns1:Major>
            <ns1:Intermediate>0</ns1:Intermediate>
            <ns1:Minor>0</ns1:Minor>
        </ns1:Version>
        <ns1:RequestTimestamp>'.$requestTimeStamp.'</ns1:RequestTimestamp>
        <ns1:Options>
            <ns1:VerifyAddresses>true</ns1:VerifyAddresses>
            <ns1:CheckResidentialStatus>true</ns1:CheckResidentialStatus>
            <ns1:DirectionalAccuracy>MEDIUM</ns1:DirectionalAccuracy>
            <ns1:ConvertToUpperCase>true</ns1:ConvertToUpperCase>
        </ns1:Options>
        <ns1:AddressesToValidate>
            <ns1:AddressId>1</ns1:AddressId>
            <ns1:Address>
                <ns1:StreetLines>'.$street1.'</ns1:StreetLines>
                <ns1:City>'.$city.'</ns1:City>
                <ns1:StateOrProvinceCode>'.$state.'</ns1:StateOrProvinceCode>
                <ns1:PostalCode>'.$postcode.'</ns1:PostalCode>
                <ns1:CountryCode>'.$countryId.'</ns1:CountryCode>
            </ns1:Address>
        </ns1:AddressesToValidate>
    </ns1:AddressValidationRequest>
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
    protected function _processResponse($response) {
        
        $return = array();
        $return['error'] = true;
        
        $responseJson = Mage::helper('addressvalidator')->xmlToObject($response);

        $body = @$responseJson->Body;
        $addressValidationReply = @$body->AddressValidationReply;
        $highestSeverity = @$addressValidationReply->HighestSeverity;
        $notifications = @$addressValidationReply->Notifications;
        $statusCode = @$notifications->Code;
        $statusMessage = @$notifications->Message;

        if ($highestSeverity == 'SUCCESS') {
            
            $addresses = $addressValidationReply->AddressResults;
            $addresses = (!is_array($addresses)) ? array($addresses) : $addresses;

            $return['error'] = false;
            $return['data'] = $addresses;

        } else {

            Mage::log($statusCode . '-' . $statusMessage, null, 'aydus_addressvalidator.log');
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
        
        foreach ($responseData as $i => $result) {

            if ($i + 1 > $this->_numResults) {

                break;
            }

            try {
                
                $proposedAddressDetails = @$result->ProposedAddressDetails;
                $score = (int)@$proposedAddressDetails->Score;
                
                if ($score > 50){
                    
                    $addressData = @$proposedAddressDetails->Address;
                    
                    $countryId = $addressData->CountryCode;
                    $country = $addressData->CountryCode;
                    $street = array($addressData->StreetLines);
                    $city = $addressData->v2City;
                    $regionModel = Mage::getModel('directory/region');
                    $regionModel->loadByCode($addressData->StateOrProvinceCode, $countryId);
                    $regionName = $regionModel->getName();
                    $regionId = $regionModel->getId();
                    $postcode = $addressData->PostalCode;
                    
                    $results[] = array(
                            'country_id' => $countryId,
                            'country' => $country,
                            'street' => $street,
                            'city' => $city,
                            'region' => $regionName,
                            'region_id' => $regionId,
                            'postcode' => $postcode,
                    );                    
                }
                

            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'aydus_addressvalidator.log');
            }
        }

        return $results;
    }

}
