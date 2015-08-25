<?php

/**
 * 
 * Address Validator observer
 * 
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */
class Aydus_Addressvalidator_Model_Observer extends Mage_Core_Model_Abstract {

    /**
     * Validate address using configured service, rewrite response if suggestions available
     * 
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer $observer
     */
    public function validateAddress($observer) {
        
        $helper = Mage::helper('addressvalidator');
        $request = Mage::app()->getRequest();
        $event = $observer->getEvent();
        $controller = $event->getControllerAction();
        $response = $controller->getResponse();
        $store = Mage::app()->getStore();
        $storeId = $store->getId();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        
        if ($event->getName() == 'controller_action_postdispatch_checkout_onepage_saveBilling') {
        
            $address = $quote->getBillingAddress();
        } else {
        
            $address = $quote->getShippingAddress();
        }
        
        //save validated address
        $addressValidated = $request->getParam('address_validated');
        if ($addressValidated) {
            if ($address->getAddressType()=='billing'){
                $postData = $request->getParam('billing');
            } else {
                $postData = $request->getParam('shipping');
            }
            $postData['customer_address_id'] = $addressValidated;
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
        $validatedAddress = Mage::getModel('aydus_addressvalidator/address');
        $validatedAddress->load($addressId, 'address_id');
        
        if ($validatedAddress->getId() && $validatedAddress->getValidated()){
            return $observer;
        }        

        if ($helper->tooManyAttempts()) {

            $result = array();
            $result['validate'] = true;
            $result['error'] = true;
            $tooManyAttemptsUrl = Mage::getStoreConfig('aydus_addressvalidator/configuration/too_many_attempts_url',$storeId);
            $result['data'] = Mage::getUrl($tooManyAttemptsUrl);
            $result['message'] = $helper->getMessaging('too_many_attempts');

            $response->setBody(Mage::helper('core')->jsonEncode($result));
            return $this;
        }

        $validateStore = $helper->validateStore($store);

        if ($validateStore) {
            
            $international = ($address->getCountryId() && Mage::getStoreConfig('general/country/default') != $address->getCountryId()) ? true : false;
            $service = $helper->getService($storeId, $international);
            $returned = array('error' => true);

            try {
                $returned = $service->getResults($address);
            } catch (Exception $e) {
                $returned['data'] = $e->getMessage();
                Mage::log($e->getMessage(), null, 'aydus_addressvalidator.log');
            }

            if ($returned['error'] === false) {
                
                $responseCode = ($helper->isDebug() && isset($returned['response_code']) && $returned['response_code']) ? ' (' . $returned['response_code'] . ')' : '';
                $result = array();
                $result['validate'] = true;
                $result['error'] = false;
                
                if (is_array($returned['data']) && count($returned['data']) > 0) {
                    
                    $result['data'] = json_encode($returned['data']);
                    $result['message'] = $helper->getMessaging('matches_available') . $responseCode;
                    
                    $autoPopulate = (int)Mage::getStoreConfig('aydus_addressvalidator/configuration/auto_populate', $storeId);
                    
                    if ($autoPopulate){
                        $helper->setAddressData($address, $returned['data'][0], true);
                        $observer->setResult($result);
                        return $observer;
                    }
                    
                } else {

                    $result['error'] = true;
                    $result['data'] = $returned['data'];
                    $result['message'] = $helper->getMessaging('invalid_address') . $responseCode;
                }

                $rates = null;
                
                if ($address->getAddressType()=='billing'){
                
                    $billing = $request->getParam('billing');
                
                    if ($billing['use_for_shipping']){
                        $rates = $quote->getShippingAddress()->collectShippingRates()->getGroupedAllShippingRates();
                    }
                }
                
                if (is_array($rates) && count($rates)>0){
                    $response->setBody(Mage::helper('core')->jsonEncode($result));
                }
                
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
            
            $validatedAddress = Mage::getModel('aydus_addressvalidator/address');
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
            
                Mage::log($e->getMessage(),null,'aydus_addressvalidator.log');
            }
            
        }
        
        return $observer;
    }

}
