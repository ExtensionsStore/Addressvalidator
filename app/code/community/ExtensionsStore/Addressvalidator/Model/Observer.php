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
		$skipValidation = (int)$request->getParam('skip_validation') || Mage::getSingleton('core/cookie')->get('skip_validation');
		if ($skipValidation){
			//if customer elected to skip validation, never validate again for this session
			if ($request->getPost('skip_validation')){
				Mage::getSingleton('core/cookie')->set('skip_validation', 1);
			}
			return $observer;
		}
		$event = $observer->getEvent();
		$controller = $event->getControllerAction();
		$response = $controller->getResponse();
		$store = Mage::app()->getStore();
		$storeId = $store->getId();
		$validateVirtualQuote = Mage::getStoreConfig('extensions_store_addressvalidator/configuration/validate_virtual_quote',$storeId);
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		if ($quote->isVirtual() && !$validateVirtualQuote){
			return $observer;
		}
		//needed by paypal 
		Mage::getSingleton('checkout/session')->setAddressValidated(false);
		$formId = $request->getParam('form_id');
		$checkoutType = $request->getParam('checkout_type');
		$checkoutType = ($checkoutType) ? $checkoutType : 'onepage';
		$eventName = strtolower($event->getName());
		$billing = $request->getParam('billing');
		$useForShipping = (isset($billing['use_for_shipping'])) ? (bool)$billing['use_for_shipping'] : false;
		if ($eventName == 'controller_action_postdispatch_checkout_onepage_savebilling' ||
				($eventName == 'controller_action_postdispatch_onestepcheckout_ajax_save_billing' && $formId == 'billing_address') ||
				$useForShipping)
		{
			$address = $quote->getBillingAddress();
		} else {
			
			$address = $quote->getShippingAddress();
		}
		
		//already validated address
		$customerAddressId = $address->getCustomerAddressId();
		if ($customerAddressId){
			$validatedAddress = Mage::getModel('extensions_store_addressvalidator/address');
			$validatedAddress->load($customerAddressId, 'address_id');
			if ($validatedAddress->getId() && $validatedAddress->getValidated()){
				return $observer;
			}
		}
		
		//get address data
		if ($address->getAddressType()=='billing' || $useForShipping){
			$postData = $request->getParam('billing');
		} else {
			$postData = $request->getParam('shipping');
		}
		
		if (!$postData && $request->getParam('av-popup')){
			$postData = $request->getParam('av-popup');
		}
		
		//validate country
		$countryId = @$postData['country_id'];
		$countryId = ($countryId) ? $countryId : $address->getCountryId();
		$countries = Mage::getStoreConfig('extensions_store_addressvalidator/configuration/countries',$storeId);
		$countries = explode(',',$countries);
		if (!in_array($countryId, $countries)){
			return $observer;
		}
		
		$addressId = $request->getParam('billing_address_id');
		if (!$addressId){
			$addressId = $request->getParam('shipping_address_id');
		}
		
		//already validated
		$addressValidated = $request->getParam('address_validated');
		$addressValidated = ($addressValidated) ? $addressValidated: @$postData['address_validated'];
		if ($addressValidated) {
			$postData['customer_address_id'] = (is_numeric($addressValidated) && $addressValidated>1) ? $addressValidated : $address->getCustomerAddressId();
			$helper->setAddressData($address, $postData, true);
			Mage::getSingleton('checkout/session')->setAddressValidated(true);//needed by paypal
			return $observer;
		}
		
		//skip validation if customer address has already been validated
		$validatedAddress = Mage::getModel('extensions_store_addressvalidator/address');
		$validatedAddress->load($addressId, 'address_id');
		if ($validatedAddress->getId() && $validatedAddress->getValidated()){
			return $observer;
		}
		
		$allowBypass = (int)Mage::getStoreConfig('extensions_store_addressvalidator/configuration/allow_bypass', $storeId);
		
		if ($helper->tooManyAttempts()) {
			
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
				$result['checkout_type'] = $checkoutType;
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
					$result['validate'] = ($allowBypass) ? false : true;
					$result['message'] = (($returned['data']) ? $returned['data'] : $helper->getMessaging('invalid_address')) . $responseCode;
				}
				
				$body = $response->getBody();
				$responseBody = json_decode($body, true);
				$responseBody = (is_array($responseBody)) ? $responseBody : array('update_content'=>$body);//paypal
				$responseBody['goto_section'] = '';
				//light checkout
				if ($checkoutType == 'lightcheckout' && isset($responseBody['section']) && $responseBody['section']=='centinel'){
					if (!$result['error'] || !$allowBypass){
						$responseBody['section'] = 'addressvalidator';
					}
				}
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
		$controllerName = $request->getControllerName();
		$actionName = $request->getActionName();
		
		if ($customerAddress->getId() && $moduleName == 'customer' && $controllerName == 'address' && $actionName == 'formPost'){
			
			$validatedAddress = Mage::getModel('extensions_store_addressvalidator/address');
			$validatedAddress->load($customerAddress->getId(), 'address_id');
			
			$datetime = Mage::getSingleton('core/date')->gmtDate();
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
	
	/**
	 * Add address js before billing block html
	 *
	 * @see core_block_abstract_to_html_after
	 * @param Varien_Event_Observer $observer
	 * @return Varien_Event_Observer $observer
	 */
	public function prependAddressJs($observer){
		$helper = Mage::helper('addressvalidator');
		$block = $observer->getBlock();
		if ($helper->enabled() && $block->getNameInLayout()=='checkout.onepage.billing'){
			$transport = $observer->getTransport();
			$html = $transport->getHtml();
			$html = '<script type="text/javascript" src="'. $block->getSkinUrl('js/extensions_store/addressvalidator/address.js').'"></script>'.$html;
			$transport->setHtml($html);
		}
		
		return $observer;
	}
	
	/**
	 * Scheduled clean up 
	 * @see log_log_clean_after
	 * @param Varien_Event_Observer $observer
	 * @return Varien_Event_Observer $observer
	 */
	public function cleanLogs($observer){
		$cleanLogsDays = (int)Mage::getStoreConfig('extensions_store_addressvalidator/configuration/cleanlog_days');
		$datetime = Mage::getSingleton('core/date')->gmtDate();
		$cleanLogsTime = strtotime($datetime . " - $cleanLogsDays DAY");
		$cleanLogDate = date('Y-m-d H:i:s', $cleanLogsTime);
		$resource = Mage::getSingleton('core/resource');
		//$read = $resource->getConnection('core_read');
		$write = $resource->getConnection('core_write');
		$tableName = $resource->getTableName('addressvalidator/response');
		$sql = "DELETE FROM $tableName WHERE date_created < '$cleanLogDate'";
		$write->query($sql);
		return $observer;
	}
}
