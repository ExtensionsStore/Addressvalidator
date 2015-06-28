<?php

/**
 * Usps service
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */
class Aydus_Addressvalidator_Model_Service_Usps extends Aydus_Addressvalidator_Model_Service_Abstract {

    /**
     * Service 
     * @var string $_service
     * @var string $_url
     */
    protected $_service = 'usps';
    protected $_url = 'http://production.shippingapis.com/ShippingAPI.dll';

    public function _construct() {
        parent::_construct();
        $this->_url = Mage::getStoreConfig('carriers/usps/gateway_url');
    }

    /**
     * Get soap request message
     * 
     * @param Mage_Customer_Model_Address $customerAddress
     * @return string
     */
    protected function _getMessage($customerAddress) {

        $storeId = Mage::app()->getStore()->getId();
        $userId = Mage::getStoreConfig('carriers/usps/userid',$storeId);
        $extractableArray = $this->_getExtractableAddressArray($customerAddress);
        extract($extractableArray);
        $regionModel = Mage::getModel('directory/region')->load($region,'default_name');
        $state = $regionModel->getCode();
        $zip5 = $postcode;
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
    <FirmName>'.$company.'</FirmName>   
    <Address1>'.$street2.'</Address1> 
    <Address2>'.$street1.'</Address2>   
    <City>'.$city.'</City>   
    <State>'.$state.'</State>   
    <Zip5>'.$zip5.'</Zip5>   
    <Zip4>'.$zip4.'</Zip4> 
  </Address>      
</AddressValidateRequest>';
        
        $fields = array('API' => 'Verify', 'XML' => $xml);
        $message = http_build_query($fields, null, '&');
        
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

        $address = @$responseJson->Address;
        $error = (@$address->Error) ? true : false;

        if (!$error) {

            $return['error'] = false;
            $return['data'] = (!is_array($address)) ? array($address) : $address;

        } else {

            Mage::log($address->Error->Number . '-' . $address->Error->Description, null, 'aydus_addressvalidator.log');
            $return['error'] = false;
            $return['data'] = $address->Error->Description;
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

                $countryId = 'US';
                $country = 'US';
                $street = array(@$addressData->Address2);
                if (@$addressData->Address1){
                    $street[] = @$addressData->Address1;
                }
                $city = $addressData->City;
                $regionModel = Mage::getModel('directory/region');
                $regionModel->loadByCode(@$addressData->State, 'US');
                $regionName = $regionModel->getName();
                $regionId = $regionModel->getId();
                $postcode = @$addressData->Zip5.'-'.@$addressData->Zip4;

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
