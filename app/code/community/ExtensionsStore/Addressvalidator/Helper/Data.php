<?php

/**
 * Address Validator helper
 * 
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_Addressvalidator_Helper_Data extends Mage_Core_Helper_Abstract {

    const MIN_ATTEMPTS = 1;
    const DEFAULT_ATTEMPTS = 3;
    const MAX_ATTEMPTS = 25;

    /**
     * 
     * @var array $_messaging
     */
    protected $_messaging;

    /**
     * Popup messaging nodes
     */
    public function __construct() {
        $messagingNodes = Mage::getConfig()->getNode('default/extensions_store_addressvalidator/messaging');
        $this->_messaging = json_decode(json_encode((array) $messagingNodes), true);
    }

    /**
     * Debug mode for local and stage
     * @return boolean
     */
    public function isDebug() {
    	$storeId = Mage::app()->getStore()->getId();
    	$debug = (bool) Mage::getStoreConfig('extensions_store_addressvalidator/configuration/debug_mode', $storeId);
        return $debug;
    }
    
    /**
     * @return boolean
     */
    public function enabled(){
    	$storeId = Mage::app()->getStore()->getId();
    	$enabled = (bool) Mage::getStoreConfig('extensions_store_addressvalidator/configuration/enabled', $storeId);
    	return $enabled;
    }

    /**
     * Validate billing/shipping address on checkout
     *
     * @param Mage_Core_Model_Store $store
     * @return boolean
     */
    public function tooManyAttempts() {
        $numAttempts = (int) Mage::getStoreConfig('extensions_store_addressvalidator/configuration/num_attempts');
        $numAttempts = ($numAttempts >= self::MIN_ATTEMPTS && $numAttempts <= self::MAX_ATTEMPTS) ? $numAttempts : self::DEFAULT_ATTEMPTS;
        $numAttempts = ($this->isDebug()) ? 99999 : $numAttempts;

        $checkoutSession = Mage::getSingleton('checkout/session');
        $numAttempted = (int) $checkoutSession->getData('num_attempted');
        $checkoutSession->setData('num_attempted', $numAttempted+1);

        return $numAttempted > $numAttempts;
    }

    /**
     * 
     * @param string $messageKey
     * @return string|boolean
     */
    public function getMessaging($messageKey) {
        if (in_array($messageKey, array_keys($this->_messaging))) {

            $message = Mage::getStoreConfig("extensions_store_addressvalidator/messaging/$messageKey");
            return $message;
        }

        return false;
    }

    /**
     * Validate billing/shipping address on checkout
     * 
     * @param Mage_Core_Model_Store $store
     * @return boolean
     */
    public function validateStore($store) {
        $storeId = $store->getId();

        $validateStore = Mage::getStoreConfig('extensions_store_addressvalidator/configuration/enabled', $storeId);

        return $validateStore;
    }

    /**
     * Get service from store config
     * 
     * @param int $storeId
     * @param bool $international
     * @return array
     */
    public function getServices($storeId, $international=false) {
        
        $services = array();
    	$service = ($international) ? 'service_international' : 'service';
        $configService = Mage::getStoreConfig('extensions_store_addressvalidator/configuration/'.$service, $storeId);
        $configService = ($configService) ? $configService : 'usps'; 
        $alias = 'extensions_store_addressvalidator/service_' . $configService;
        $services['service'] = Mage::getModel($alias);
        
        if (Mage::getStoreConfig('extensions_store_addressvalidator/configuration/service2', $storeId)){
        	$configService2 = Mage::getStoreConfig('extensions_store_addressvalidator/configuration/service2', $storeId);
        	$alias = 'extensions_store_addressvalidator/service_' . $configService2;
        	$services['service2'] = Mage::getModel($alias);        	
        }

        return $services;
    }

    /**
     * 
     * @param Mage_Customer_Model_Address|Mage_Sales_Model_Quote_Address $customerAddress
     * @return array
     */
    public function getExtractableAddressArray($customerAddress) {
        $firstname = $customerAddress->getFirstname();
        $lastname = $customerAddress->getLastname();
        $name = $firstname . " " . $lastname;
        $name = trim($name);
        $email = $customerAddress->getEmail();
        $company = $customerAddress->getCompany();
        $street = $customerAddress->getStreet();
        $street1 = $street[0];
        $street2 = (isset($street[1])) ? $street[1] : '';
        $city = $customerAddress->getCity();
        $region = $customerAddress->getRegion();
        $countryId = $customerAddress->getCountryId();
        $postcode = $customerAddress->getPostcode();
        if ($countryId == 'US' && strlen($postcode)<5) {
            $postcode = str_pad($postcode, 5, '0');
        }
        $telephone = $customerAddress->getTelephone();

        $extractableArray = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'name' => $name,
            'email' => $email,
            'company' => $company,
            'street' => $street,
            'street1' => $street1,
            'street2' => $street2,
            'city' => $city,
            'region' => $region,
            'postcode' => $postcode,
            'country_id' => $countryId,
            'countryId' => $countryId,
            'telephone' => $telephone,
        );

        return $extractableArray;
    }

    /**
     * Create object from XML
     * 
     * @param string $xmlString
     * @param bool $replaceNamespaces
     * @return stdClass
     */
    public function xmlToObject($xmlString, $replaceNamespaces=true) {
    	if ($replaceNamespaces){
	        //replace namespaces
	        $xmlString = preg_replace('/xmlns[^=]*="[^"]*"\s*/i', '', $xmlString);
	        $xmlString = preg_replace("/(<\/?)([^:]+):([^>]*>)/", "$1$3", $xmlString);
    	}

        $xml = simplexml_load_string($xmlString);
        $xmlJsonStr = json_encode($xml);
        $xmlObject = json_decode($xmlJsonStr);

        return $xmlObject;
    }
    
    /**
     * @param Mage_Customer_Model_Address|Mage_Sales_Model_Quote_Address $address
     * @return array
     */
    public function validateAddress($address)
    {
        $storeId = Mage::app()->getStore()->getId();
        
        $international = ($address->getCountryId() && Mage::getStoreConfig('general/country/default') != $address->getCountryId()) ? true : false;
        $service = $this->getServices($storeId, $international);
        
        $return = array('error' => true);
        
        try {
            $return = $service->getResults($address);
        } catch (Exception $e) {
            $return['data'] = $e->getMessage();
            Mage::log($e->getMessage(), Zend_log::DEBUG, 'extensions_store_addressvalidator.log');
        }

        return $return;
    }
    
    /**
     * Auto populate data
     * 
     * @param Mage_Customer_Model_Address $address
     * @param array $data
     * @param bool $saveQuoteAddress
     * @return bool 
     */
    public function setAddressData($address, $data, $saveQuoteAddress=true)
    {
        try {
            
            $request = Mage::app()->getRequest();
            
            if ($address->getAddressType()=='billing'){
            
                $postData = $request->getParam('billing');
            
            } else {
            
                $postData = $request->getParam('shipping');
            }
            
            $regionId = (int)@$postData['region_id'];
            
            if ($regionId && $regionId != @$data['region_id']){
                
                Mage::log('Posted region is not the same as validated region.', Zend_log::DEBUG, 'extensions_store_addressvalidator.log');
                Mage::log($postData,Zend_log::DEBUG,'extensions_store_addressvalidator.log');
                Mage::log($data,Zend_log::DEBUG,'extensions_store_addressvalidator.log');
                return false;
            }
                        
            if (isset($data['street']) && is_array($data['street']) && count($data['street']) > 0){
                $street = (count($data['street']) > 1) ? implode("\n",$data['street']) : $data['street'][0];
                $data['street'] = $street;
            }
            
            $address->addData($data);
            if ($saveQuoteAddress){
                $address->save();
            }
            
            $customerAddressId = (int)$address->getCustomerAddressId();
            
            if ($customerAddressId){
                                
                $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
                $customerAddress->addData($data);
                $customerAddress->save();   
                
                if ($customerAddress->getId()){
                    
                    $datetime = date('Y-m-d H:i:s');
                    
                    $validatedAddress = Mage::getModel('extensions_store_addressvalidator/address');
                    $validatedAddress->load($customerAddress->getId(), 'address_id');
                    $validatedAddress->setAddressId($customerAddress->getId());
                    $validatedAddress->setAddressType($customerAddress->getAddressType());
                    $validatedAddress->setValidated(1);
                    if (!$validatedAddress->getId()){
                        $validatedAddress->setDateCreated($datetime);
                    }
                    $validatedAddress->setDateUpdated($datetime);
                    $validatedAddress->save();                    
                }

            }
            
            if ($address->getAddressType() == 'billing'){
                                
                if (@$postData['use_for_shipping']){
                    
                    unset($data['address_id']);
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $shippingAddress = $quote->getShippingAddress();
                    
                    $shippingAddress->setSameAsBilling(true);
                    $shippingAddress->addData($data);
                    $shippingAddress->save();  

                    $customerShippingAddressId = (int)$shippingAddress->getCustomerAddressId();
                    
                    if ($customerShippingAddressId){
                    
                        $customerShippingAddress = Mage::getModel('customer/address')->load($customerAddressId);
                        $customerShippingAddress->addData($data);
                        $customerShippingAddress->save();
                        
                        if ($customerShippingAddress->getId()){
                            
                            $datetime = date('Y-m-d H:i:s');
                            
                            $validatedShippingAddress = Mage::getModel('extensions_store_addressvalidator/address');
                            $validatedShippingAddress->load($customerShippingAddress->getId(), 'address_id');
                            $validatedShippingAddress->setAddressId($customerShippingAddress->getId());
                            $validatedShippingAddress->setAddressType($customerShippingAddress->getAddressType());
                            $validatedShippingAddress->setValidated(1);
                            if (!$validatedShippingAddress->getId()){
                                $validatedShippingAddress->setDateCreated($datetime);
                            }
                            $validatedShippingAddress->setDateUpdated($datetime);
                            $validatedShippingAddress->save();                            
                        }
                        
                    }                    
                    
                }
                
            }
            
        } catch(Exception $e){
            Mage::log($e->getMessage(),Zend_Log::DEBUG, 'extensions_store_addressvalidator.log');
            return false;
        }
        
        return true;
        
    }

}
