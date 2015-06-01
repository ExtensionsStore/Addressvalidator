<?php

/**
 * Address Validator helper
 * 
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */
class Aydus_Addressvalidator_Helper_Data extends Mage_Core_Helper_Abstract {

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
        $messagingNodes = Mage::getConfig()->getNode('default/addressvalidator/messaging');
        $this->_messaging = json_decode(json_encode((array) $messagingNodes), true);
    }

    /**
     * Debug mode for local and stage
     * @return boolean
     */
    public function isDebug() {
        $httpHost = Mage::app()->getFrontController()->getRequest()->getHttpHost();

        $debug = (preg_match('/trunk|local|stage/', $httpHost)) ? true : false;

        return $debug;
    }

    /**
     * Validate billing/shipping address on checkout
     *
     * @param Mage_Core_Model_Store $store
     * @return boolean
     */
    public function tooManyAttempts() {
        $numAttempts = (int) Mage::getStoreConfig('addressvalidator/configuration/num_attempts');
        $numAttempts = ($numAttempts >= self::MIN_ATTEMPTS && $numAttempts <= self::MAX_ATTEMPTS) ? $numAttempts : self::DEFAULT_ATTEMPTS;
        $numAttempts = ($this->isDebug()) ? 200 : $numAttempts;

        $checkoutSession = Mage::getSingleton('checkout/session');
        $numAttempted = (int) $checkoutSession->getNumAttempted();
        $checkoutSession->setNumAttempted($numAttempted + 1);

        return $numAttempted > $numAttempts;
    }

    /**
     * 
     * @param string $messageKey
     * @return string|boolean
     */
    public function getMessaging($messageKey) {
        if (in_array($messageKey, array_keys($this->_messaging))) {

            $message = Mage::getStoreConfig("addressvalidator/messaging/$messageKey");
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

        $validateStore = Mage::getStoreConfig('addressvalidator/configuration/enabled', $storeId);

        return $validateStore;
    }

    /**
     * Get service from store config
     * 
     * @param int $storeId
     * @return Aydus_Addressvalidator_Model_Service_Abstract
     */
    public function getService($storeId) {
        $configService = Mage::getStoreConfig('addressvalidator/configuration/service', $storeId);
        $alias = 'addressvalidator/service_' . $configService;
        $serviceModel = Mage::getModel($alias);

        return $serviceModel;
    }

    /**
     * 
     * @param Mage_Customer_Model_Address $customerAddress
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
        $postcode = $customerAddress->getPostcode();
        $countryId = $customerAddress->getCountryId();
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
     * @return stdClass
     */
    public function xmlToObject($xmlString) {
        //replace namespaces
        $xmlString = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xmlString);

        $xml = simplexml_load_string($xmlString);
        $xmlJsonStr = json_encode($xml);
        $xmlObject = json_decode($xmlJsonStr);

        return $xmlObject;
    }

}
