<?php

/**
 * Abstract Service
 * 
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */
abstract class ExtensionsStore_Addressvalidator_Model_Service_Abstract extends Mage_Core_Model_Abstract {

    /**
     * Database
     */
    protected $_read;
    protected $_write;

    /**
     * Errors
     */
    const NO_MESSAGE = 'Could not get message';
    const NO_RESPONSE = 'Could not get response';
    const NO_RESPONSE_DATA = 'Could not process response';
    const INVALID_ADDRESS = 'Invalid address';
    const NO_RESULTS = 'Could not get results';

    /**
     * Current request
     *  @var string $_hash
     *  @var array $_requestData
     */
    protected $_hash;
    protected $_requestData;

    /**
     * Service 
     * @var string $_service
     * @var string $_url
     * @var array $_headers
     */
    protected $_service;
    protected $_url;
    protected $_headers;

    /**
     * Config
     * @var int $_numResults
     */
    protected $_numResults;

    /**
     * Properties needed by this abstract
     * @var string $_country
     */
    protected $_country;

    public function _construct() {
        $this->_numResults = Mage::getStoreConfig('extensions_store_addressvalidator/configuration/num_results', 0);
        $resource = Mage::getSingleton('core/resource');
        $this->_read = $resource->getConnection('core_read');
        $this->_write = $resource->getConnection('core_write');
    }

    /**
     * 
     * @param Mage_Customer_Model_Address $customerAddress
     * @return string|array Error|Results
     */
    public function getResults($customerAddress) {
        $return = array();
        $return['error'] = true;
        $prefix = Mage::getConfig()->getTablePrefix();
        $table = $prefix . 'extensions_store_addressvalidator_responses';

        //get soap request message for customer address
        $message = $this->_getMessage($customerAddress);

        if ($message) {

            //get soap response
            $response = $this->_getResponse($message);

            if ($response) {

                //process response, extracting results
                $processedResponse = $this->_processResponse($response);

                if (!$processedResponse['error']) { //means internal error

                    $return['error'] = false;

                    if (isset($processedResponse['response_code']) && $processedResponse['response_code']) {
                        $responseCode = $processedResponse['response_code'];
                        $return['response_code'] = $responseCode;
                        $values = array( 'responsecode' => $responseCode, 'hash' => $this->_hash );
                        $this->_write->query("UPDATE $table SET response_code = :responsecode WHERE hash = :hash", $values);
                    }

                    $resultsData = $processedResponse['data']; //empty if invalid address

                    if (is_array($resultsData)) {

                        //process results into array
                        $results = $this->_processResults($resultsData);


                        if (is_array($results) && count($results) > 0) {

                            $return['data'] = $results;
                        } else {

                            $return['data'] = self::NO_RESULTS;
                        }
                    } else if (is_string($resultsData)) {

                        $return['data'] = $resultsData;
                        
                    } else {
                    	
                    	$return['data'] = self::INVALID_ADDRESS;
                    }
                } else {

                    $return['data'] = self::NO_RESPONSE_DATA;
                }
            } else {

                $return['data'] = self::NO_RESPONSE;
            }
        } else {

            $return['data'] = self::NO_MESSAGE;
        }

        return $return;
    }

    /**
     * Get soap request message
     * 
     * @param Mage_Customer_Model_Address $customerAddress
     * @return array
     */
    abstract protected function _getMessage($customerAddress);

    protected function _getExtractableAddressArray($customerAddress) {
        $extractableArray = Mage::helper('addressvalidator')->getExtractableAddressArray($customerAddress);
        $this->_requestData = $extractableArray;
        extract($extractableArray);

        $this->_country = Mage::getModel('directory/country')->load($countryId);

        return $extractableArray;
    }

    /**
     * Get response for request message from cache or make call
     * 
     * @param string $message
     * @return string
     */
    protected function _getResponse($message) {
        $hash = md5($message);
        $this->_hash = $hash;
        $prefix = Mage::getConfig()->getTablePrefix();
        $table = $prefix . 'extensions_store_addressvalidator_responses';

        $response = $this->_read->fetchOne("SELECT UNCOMPRESS(response) FROM $table WHERE hash = '$hash'");

        if (!$response) {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $this->_url);
            $headers = array(
                'Content-Type: application/xml; charset=utf-8',
                'Content-Length: ' . strlen($message),
            );
            if (is_array($this->_headers) && count($this->_headers) > 0) {
                $headers = array_merge($headers, $this->_headers);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {

                extract($this->_requestData);

                $address = array(
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "name" => $name,
                    "email" => $email,
                    "company" => $company,
                    "street1" => $street1,
                    "street2" => $street2,
                    "city" => $city,
                    "region" => $region,
                    "postcode" => $postcode,
                    "country_id" => $country_id,
                    "telephone" => $telephone,
                );
                $service = $this->_service;
                $addressFields = implode(',', array_keys($address));
                //$addressValues = implode("','", $address);
                $addressValues = array();
                foreach( $address as $key => &$value ) {
                	$addressValues[] = ':' . $key;
                	if( $value === null )
                		$value = '';
                }
                $addressValues	= implode(",", $addressValues);
                $dateCreated = date('Y-m-d H:i:s');
                $storeId = Mage::app()->getStore()->getId();
                
                $values = array( 'hash' => $hash, 'response' => $response, 'service' => $service, 'storeId' => $storeId, 'dateCreated' => $dateCreated );
                $values	= array_merge( $values, $address );
                
                $this->_write->query("REPLACE INTO $table (hash, response, service, $addressFields, store_id, date_created) VALUES(:hash, COMPRESS(:response), :service, $addressValues, :storeId, :dateCreated)", $values );
	        } else {
        		$errorMessage = curl_error($ch);
        		Mage::log($errorMessage, Zend_log::ERR, 'extensions_store_addressvalidator.log');
        	}
        }

        return $response;
    }

    /**
     * Process response string into json object and extract addresses array
     * 
     * @param string $response
     * @return array
     */
    abstract protected function _processResponse($response);

    /**
     * Generate array of addresses
     * 
     * @param array $responseData
     * @return array
     */
    abstract protected function _processResults(array $responseData);
}
