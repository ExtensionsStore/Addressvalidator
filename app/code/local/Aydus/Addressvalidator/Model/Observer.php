<?php

/**
 * 
 * Address Validator observer
 * 
 * @category   Aydus
 * @package	   Aydus_Addressvalidator
 * @author     Aydus Consulting <davidt@aydus.com>
 */

class Aydus_Addressvalidator_Model_Observer extends Mage_Core_Model_Abstract
{
	
	/**
	 * Validate address using configured service, rewrite response if suggestions available
	 * 
     * @param Varien_Event_Observer $observer
	 */
	public function validateAddress($observer)
	{
		$request = Mage::app()->getRequest();
		//skip validation if already validated
		$addressValidated = $request->getParam('address_validated');
		if ($addressValidated){
			return $this;
		}
		
		$event = $observer->getEvent();
		$controller = $event->getControllerAction();
		$response = $controller->getResponse();
		$store = Mage::app()->getStore();
		$storeId = $store->getId();
		
		$helper = Mage::helper('addressvalidator');
		
		if ($helper->tooManyAttempts()){
			
			$result = array();
			$result['validate'] = true;
			$result['error'] = true;
			$result['data'] = Mage::getUrl('customer-service');
			$result['message'] = $helper->getMessaging('too_many_attempts');
				
			$response->setBody(Mage::helper('core')->jsonEncode($result));
			return $this;
		}
		
		$validateStore = $helper->validateStore($store);
				
		if ($validateStore){
			
			$event = $observer->getEvent();
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			
			if ($event->getName () == 'controller_action_postdispatch_checkout_onepage_saveBilling') {
					
				$address = $quote->getBillingAddress();
				
			} else {
					
				$address = $quote->getShippingAddress();
			}
			
			$service = $helper->getService($storeId);
			$returned = array('error'=>true);
			
			try {
				$returned = $service->getResults($address);		
			} catch(Exception $e){
				Mage::log($e->getMessage(),null,'addressvalidator.log');
			}

			if (!$returned['error']){
				
				$responseCode = ($helper->isDebug() && isset($returned['response_code']) && $returned['response_code']) ? ' ('.$returned['response_code'].')' : ''; 
				$result = array();
				$result['validate'] = true;
				$result['error'] = false;
				
				if (is_array($returned['data']) && count($returned['data'])>0){
					
					$result['data'] = json_encode($returned['data']);
					$result['message'] = $helper->getMessaging('matches_available').$responseCode;
						
				} else {
					
					$result['error'] = true;
					$result['data'] = $returned['data'];
					$result['message'] = $helper->getMessaging('invalid_address').$responseCode;
				}
				
				$response->setBody(Mage::helper('core')->jsonEncode($result));
			} 
				
		}
				
		return $this;
	}
    
}