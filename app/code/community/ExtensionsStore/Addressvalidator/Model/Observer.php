<?php

/**
 * 
 * Address Validator observer
 * 
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_Addressvalidator_Model_Observer extends Mage_Core_Model_Abstract {

    /**
     * Validate address using configured service, rewrite response if suggestions available
     * 
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer $observer
     */
    public function validateAddress($observer) {
        
        $helper = Mage::helper('addressvalidator');
        if (!$helper->enabled()){
        	return $observer;
        }
        $request = Mage::app()->getRequest();
        $event = $observer->getEvent();
        $controller = $event->getControllerAction();
        $response = $controller->getResponse();
        $store = Mage::app()->getStore();
        $storeId = $store->getId();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $formId = $request->getParam('form_id');
        $oneStepCheckout = ($formId == 'billing_address' || $formId == 'shipping_address') ? true : false;
        $eventName = strtolower($event->getName());
        
        if ($eventName == 'controller_action_postdispatch_checkout_onepage_savebilling' ||
        	($eventName == 'controller_action_postdispatch_onestepcheckout_ajax_save_billing' && $formId == 'billing_address')) 
        {
            $address = $quote->getBillingAddress();
        } else {
        
            $address = $quote->getShippingAddress();
        }
        
        //get address data
        if ($address->getAddressType()=='billing'){
        	$postData = $request->getParam('billing');
        } else {
            $postData = $request->getParam('shipping');
        }        
        
        //validate country
        $countryId = @$postData['country_id'];
        $countries = Mage::getStoreConfig('extensions_store_addressvalidator/configuration/countries',$storeId);
        $countries = explode(',',$countries);
        if (!in_array($countryId, $countries)){
        	return $observer;
        }
        
        //already validated
        $addressValidated = @$postData['address_validated'];
        if ($addressValidated) {
        	$postData['customer_address_id'] = (is_numeric($addressValidated) && $addressValidated>1) ? $addressValidated : NULL;
            $helper->setAddressData($address, $postData, true);
            return $observer;
        }
        
        //customer elected to skip validation
        $skipValidation = (int)$request->getParam('skip_validation');
        if ($skipValidation){
            return $observer;
        }
        
        //skip validation if customer address has already been validated
        $addressId = $request->getParam('billing_address_id');
        if (!$addressId){
            $addressId = $request->getParam('shipping_address_id');
        }
        $validatedAddress = Mage::getModel('extensions_store_addressvalidator/address');
        $validatedAddress->load($addressId, 'address_id');
        
        if ($validatedAddress->getId() && $validatedAddress->getValidated()){
            return $observer;
        }        

        if ($helper->tooManyAttempts()) {
        	
        	$allowBypass = (int)Mage::getStoreConfig('extensions_store_addressvalidator/configuration/allow_bypass', $storeId);
            
            if ($allowBypass){
            	$result = array();
            	$result['validate'] = true;
            	$result['error'] = true;
            	$tooManyAttemptsUrl = Mage::getStoreConfig('extensions_store_addressvalidator/configuration/too_many_attempts_url',$storeId);
            	$result['data'] = Mage::getUrl($tooManyAttemptsUrl);
            	$result['message'] = $helper->getMessaging('too_many_attempts');
            	
            	$body = $response->getBody();
            	$responseBody = json_decode($body, true);
            	$responseBody = (is_array($responseBody)) ? $responseBody : array();
            	$responseBody['goto_section'] = '';
            	$responseBody['address_validator'] = $result;
            	 
            	$response->setBody(Mage::helper('core')->jsonEncode($responseBody));
            }
            
            return $observer;
        }

        $validateStore = $helper->validateStore($store);

        if ($validateStore) {
            
            $international = ($address->getCountryId() && Mage::getStoreConfig('general/country/default') != $address->getCountryId()) ? true : false;
            $services = $helper->getServices($storeId, $international);
            $returns = array();

            try {
            	foreach ($services as $key=>$service){
            		$returns[$key] = $service->getResults($address);
            	}
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::DEBUG, 'extensions_store_addressvalidator.log');
            }
            
            if (count($returns)>0){
            	
            	$returned = $returns['service'];
            	$returned2 = (isset($returns['service2'])) ? $returns['service2'] : array('error' => true);
            	
            	$result = array();
            	if (!$formId){
            		$formId = 'co-'.$address->getAddressType().'-form';
            	}
            	$result['form_id'] = $formId;
            	$result['validate'] = true;
            	$result['error'] = $returned['error'];
            	$responseCode = ($helper->isDebug() && isset($returned['response_code']) && $returned['response_code']) ? ' (' . $returned['response_code'] . ')' : '';
            	
            	if (is_array($returned['data']) && count($returned['data']) > 0) {

            		if ($returned2['error'] === false && is_array($returned2['data']) && count($returned2['data']) > 0 && $returned['data'] != $returned2['data']){
            			$returned['data'] = array_merge($returned['data'], $returned2['data']);
            		}
            		
            		$result['data'] = json_encode($returned['data']);
            		$result['message'] = $helper->getMessaging('matches_available') . $responseCode;
            	
            		$autoPopulate = (int)Mage::getStoreConfig('extensions_store_addressvalidator/configuration/auto_populate', $storeId);
            	
            		if ($autoPopulate){
            			$helper->setAddressData($address, $returned['data'][0], true);
            			$result['validate'] = false;
            		}
            	
            	} else {
            	
            		$result['error'] = true;
            		$result['data'] = $returned['data'];
            		$result['message'] = (($returned['data']) ? $returned['data'] : $helper->getMessaging('invalid_address')) . $responseCode;
            	}
            	
            	$body = $response->getBody();
            	$responseBody = json_decode($body, true);
            	$responseBody = (is_array($responseBody)) ? $responseBody : array('update_content'=>$body);//paypal
            	$responseBody['goto_section'] = '';
            	$responseBody['address_validator'] = $result;
            	 
            	$response->setBody(Mage::helper('core')->jsonEncode($responseBody));
            	 
            	$observer->setResult($result);
            	
            }

        }

        return $observer;
    }
    
    /**
     * Unflag customer address previously validated
     * 
     * @see customer_address_save_after
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer $observer
     */
    public function updateAddressValidated($observer)
    {
        $customerAddress = $observer->getCustomerAddress();
        $request = Mage::app()->getRequest();
        $moduleName = $request->getModuleName();
        
        if ($customerAddress->getId() && $moduleName != 'checkout'){
            
            $validatedAddress = Mage::getModel('extensions_store_addressvalidator/address');
            $validatedAddress->load($customerAddress->getId(), 'address_id');
            
            $datetime = date('Y-m-d H:i:s');
            $validatedAddress->setAddressId($customerAddress->getId());
            $validatedAddress->setValidated(0);
            
            if (!$validatedAddress->getId()){
                $validatedAddress->setDateCreated($datetime);
            }
            $validatedAddress->setDateUpdated($datetime);
            
            try {
            
                $validatedAddress->save();
            
            }catch (Exception $e){
            
                Mage::log($e->getMessage(),Zend_Log::DEBUG,'extensions_store_addressvalidator.log');
            }
            
        }
        
        return $observer;
    }

}
