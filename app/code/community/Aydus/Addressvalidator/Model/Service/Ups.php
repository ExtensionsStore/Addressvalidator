<?php

/**
 * Ups service
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */
class Aydus_Addressvalidator_Model_Service_Ups extends Aydus_Addressvalidator_Model_Service_Abstract {

    /**
     * Service 
     * @var string $_service
     * @var string $_url
     */
    protected $_service = 'ups';
    protected $_url = 'https://onlinetools.ups.com/ups.app/xml/XAV';

    public function _construct() {
        parent::_construct();
        $storeId = Mage::app()->getStore()->getId();
        $url = Mage::getStoreConfig('aydus_addressvalidator/ups/url',$storeId);
        if ($url){
            $this->_url = $url;
        }
    }

    /**
     * Get soap request message
     * 
     * @param Mage_Customer_Model_Address $customerAddress
     * @return string
     */
    protected function _getMessage($customerAddress) {
        
        $storeId = Mage::app()->getStore()->getId();
        $accessLicenseNumber = Mage::helper('core')->decrypt(Mage::getStoreConfig('aydus_addressvalidator/ups/access_license_number',$storeId));
        $userId = Mage::helper('core')->decrypt(Mage::getStoreConfig('aydus_addressvalidator/ups/user_id',$storeId));
        $password = Mage::helper('core')->decrypt(Mage::getStoreConfig('aydus_addressvalidator/ups/password',$storeId));

        $extractableArray = $this->_getExtractableAddressArray($customerAddress);
        extract($extractableArray);
        
        $regionModel = Mage::getModel('directory/region')->load($region,'default_name');
        $state = $regionModel->getCode();

        $message = '<?xml version="1.0"	?>
        <AccessRequest xml:lang="en-US">
          <AccessLicenseNumber>'.$accessLicenseNumber.'</AccessLicenseNumber>
          <UserId>'.$userId.'</UserId>
          <Password>'.$password.'</Password>
        </AccessRequest>

        <?xml version="1.0"?>
        <AddressValidationRequest xml:lang="en-US">
        <Request>
        <TransactionReference>
        <CustomerContext/>
        <XpciVersion>1.0</XpciVersion>
        </TransactionReference>
        <RequestAction>XAV</RequestAction>
        <RequestOption>3</RequestOption>
        </Request>

        <AddressKeyFormat>
        <AddressLine>'.$street1.'</AddressLine>	
        <AddressLine>'.$street2.'</AddressLine>	
        <PoliticalDivision2>'.$city.'</PoliticalDivision2>
        <PoliticalDivision1>'.$state.'</PoliticalDivision1>
        <PostcodePrimaryLow>'.$postcode.'</PostcodePrimaryLow>
        <CountryCode>'.$countryId.'</CountryCode>
        </AddressKeyFormat>
        </AddressValidationRequest>';            

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

        $responseStatusCode = $responseJson->Response->ResponseStatusCode;

        if ($responseStatusCode == 1) {

            $return['error'] = false;
            $return['data'] = (!is_array($responseJson->AddressKeyFormat)) ? array($responseJson->AddressKeyFormat) : $responseJson->AddressKeyFormat;

        } else {
            
            $error = @$responseJson->Response->Error;
            $message = 'An error occurred during service call to UPS.';
            
            if ($error && $error->ErrorCode && $error->ErrorDescription){
                $message = $error->ErrorCode.'-'.$error->ErrorDescription;
            }

            Mage::log($message, null, 'aydus_addressvalidator.log');
            $return['error'] = false;
            $return['data'] = $message;
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
        
        foreach ($responseData as $i => $addressData) {

            if ($i + 1 > $this->_numResults) {

                break;
            }

            try {

                $countryId = $addressData->CountryCode;
                $country = $addressData->CountryCode;
                $street = array($addressData->AddressLine);
                $city = $addressData->PoliticalDivision2;
                $regionModel = Mage::getModel('directory/region');
                $regionModel->loadByCode($addressData->PoliticalDivision1, $countryId);
                $regionName = $regionModel->getName();
                $regionId = $regionModel->getId();
                $postcode = $addressData->PostcodePrimaryLow.'-'.$addressData->PostcodeExtendedLow;

                $results[] = array(
                    'country_id' => $countryId,
                    'country' => $country,
                    'street' => $street,
                    'city' => $city,
                    'region' => $regionName,
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
