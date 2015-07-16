<?php 

/**
 * Model test
 *
 * @category    Aydus
 * @package     Aydus_Addressvalidator
 * @author      Aydus <davidt@aydus.com>
 */

class Aydus_Addressvalidator_Test_Model_TestModel extends EcomDev_PHPUnit_Test_Case_Config
{	

    /**
     * 
     * @test 
     * @loadFixture testModel.yaml
     */
    public function testObserver()
    {
        echo "\nAydus_Addressavalidator model test started.";
        
        $observerModel = Mage::getModel('aydus_addressvalidator/observer');
        $quote = Mage::getSingleton('sales/quote');
        $quote->load(1);
        $billingAddress = Mage::getModel('sales/quote_address');
        $billingAddress->load(1);
        $quote->setBillingAddress($billingAddress);
        $shippingAddress = Mage::getModel('sales/quote_address');
        $shippingAddress->load(2);
        $quote->setShippingAddress($shippingAddress);
        
        $checkoutSession = $this->getModelMock('checkout/session');
        $checkoutSession->expects($this->any())->method('getQuote')->will($this->returnValue($quote));
        $this->replaceByMock('singleton', 'checkout/session', $checkoutSession);
        
        $observer = new Varien_Event_Observer();
        $event = new Varien_Event();
        $controller = Mage::app()->getFrontController();
        $response = Mage::app()->getResponse();
        $controller->setResponse($response);
        $event->setControllerAction($controller);
        $observer->setEvent($event);
        
        $this->assertEventObserverDefined(
                'frontend', 'controller_action_postdispatch_checkout_onepage_saveBilling', 'aydus_addressvalidator/observer', 'validateAddress'
        );
        $event->setName('controller_action_postdispatch_checkout_onepage_saveBilling');
        
        $observer = $observerModel->validateAddress($observer);
        
        $result = $observer->getResult();
        $noError = ($result['error'] === false) ? true : false;
        
        $this->assertTrue($noError);
        
        $this->assertEventObserverDefined(
                'frontend', 'controller_action_postdispatch_checkout_onepage_saveShipping', 'aydus_addressvalidator/observer', 'validateAddress'
        );
        $event->setName('controller_action_postdispatch_checkout_onepage_saveShipping');
        
        $observer = $observerModel->validateAddress($observer);
        
        $result = $observer->getResult();
        $noError = ($result['error'] === false) ? true : false;
        $this->assertTrue($noError);
                
        echo "\nAydus_Addressavalidator model test completed.";
    }
	
}