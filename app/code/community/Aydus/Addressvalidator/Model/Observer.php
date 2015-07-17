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
     */
    public function validateAddress($observer) {
        $request = Mage::app()->getRequest();
        //skip validation if already validated
        $addressValidated = $request->getParam('address_validated');
        if ($addressValidated) {
            return $this;
        }

        $event = $observer->getEvent();
        $controller = $event->getControllerAction();
        $response = $controller->getResponse();
        $store = Mage::app()->getStore();
        $storeId = $store->getId();

        $helper = Mage::helper('addressvalidator');

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

            $event = $observer->getEvent();
            $quote = Mage::getSingleton('checkout/session')->getQuote();

            if ($event->getName() == 'controller_action_postdispatch_checkout_onepage_saveBilling') {

                $address = $quote->getBillingAddress();
            } else {

                $address = $quote->getShippingAddress();
            }

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
                        if ($helper->setAddressData($address, $returned['data'][0])){
                            $observer->setResult($result);
                            return $observer;
                        }
                    }
                    
                } else {

                    $result['error'] = true;
                    $result['data'] = $returned['data'];
                    $result['message'] = $helper->getMessaging('invalid_address') . $responseCode;
                }

                $response->setBody(Mage::helper('core')->jsonEncode($result));
                
                $observer->setResult($result);
            }
        }

        return $observer;
    }

}
