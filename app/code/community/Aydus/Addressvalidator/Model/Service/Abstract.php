<?php

/**
 * Abstract Service
 * 
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */
abstract class Aydus_Addressvalidator_Model_Service_Abstract extends Mage_Core_Model_Abstract {

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
        $this->_numResults = Mage::getStoreConfig('aydus_addressvalidator/configuration/num_results', 0);
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
        $table = $prefix . 'aydus_addressvalidator_responses';

        //get soap request message for customer address
        $message = $this->_getMessage($customerAddress);

        if ($message) {

            //get soap response
            $response = $this->_getResponse($message);

            if ($response) {

                //process response, extracting results
                $processedResponse = $this->_processResponse($response);

                if (!$processedResponse['error']) {

                    $return['error'] = false;

                    if (isset($processedResponse['response_code']) && $processedResponse['response_code']) {
                        $responseCode = $processedResponse['response_code'];
                        $return['response_code'] = $responseCode;
                        $this->_write->query("UPDATE $table SET response_code = '$responseCode' WHERE hash = '{$this->_hash}'");
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
        $table = $prefix . 'aydus_addressvalidator_responses';

        $response = $this->_read->fetchOne("SELECT UNCOMPRESS(response) FROM $table WHERE hash = '$hash'");

        if (!$response) {

            $ch = curl_init();
            //log curl errors
            $f = fopen('var/log/aydus_addressvalidator.log', 'w');

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
            fclose($f);
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
                $addressValues = implode("','", $address);
                $dateCreated = date('Y-m-d H:i:s');
                $storeId = Mage::app()->getStore()->getId();
                $this->_write->query("REPLACE INTO $table (hash, response, service, $addressFields, store_id, date_created) VALUES('$hash', COMPRESS('$response'), '$service', '$addressValues', '$storeId', '$dateCreated')");
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
