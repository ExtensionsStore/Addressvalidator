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
    protected $_numAttempted = 0;
    
    protected function _mockQuote()
    {
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
        $checkoutSession->expects($this->any())->method('getData')->with('num_attempted')->will($this->returnCallback(array($this, 'getNumAttempted')));
        $checkoutSession->expects($this->any())->method('setData')->will($this->returnCallback(array($this, 'setNumAttempted')));
        $this->replaceByMock('singleton', 'checkout/session', $checkoutSession);
        
        $this->assertEquals($quote, $checkoutSession->getQuote());
    }
    
    public function getNumAttempted()
    {
        return $this->_numAttempted;
    }
    
    public function setNumAttempted()
    {
        $args = func_get_args();
        $key = $args[0];
        $value = $args[1];
        
        $this->_numAttempted = $value;
    }

    /**
     * 
     * @test 
     * @loadFixture testModel.yaml
     */
    public function testObserver()
    {
        echo "\nAydus_Addressavalidator model test started..";
        $this->_mockQuote();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $observerModel = Mage::getModel('aydus_addressvalidator/observer');
        $observer = new Varien_Event_Observer();
        $event = new Varien_Event();
        $controller = Mage::app()->getFrontController();
        $response = Mage::app()->getResponse();
        $controller->setResponse($response);
        $event->setControllerAction($controller);
        $observer->setEvent($event);
        
        //validate billing address
        $this->assertEventObserverDefined(
                'frontend', 'controller_action_postdispatch_checkout_onepage_saveBilling', 'aydus_addressvalidator/observer', 'validateAddress'
        );
        $event->setName('controller_action_postdispatch_checkout_onepage_saveBilling');

        $billingAddress = $quote->getBillingAddress();
        Mage::app()->getRequest()->setMethod('POST')
        ->setPost('billing', $billingAddress->getData());
        
        $observer = $observerModel->validateAddress($observer);
        
        $result = $observer->getResult();
        $noError = ($result['error'] === false) ? true : false;
        
        $this->assertTrue($noError);
        $data = json_decode($result['data'],true);
        $hasData = (is_array($data) && count($data)>0) ? true : false;
        $this->assertTrue($hasData);
        
        $postcode = $data[0]['postcode'];
        $populated = ($billingAddress->getPostcode() === $postcode) ? true : false;
        $this->assertTrue($populated);
        
        //validate shipping address
        $this->assertEventObserverDefined(
                'frontend', 'controller_action_postdispatch_checkout_onepage_saveShipping', 'aydus_addressvalidator/observer', 'validateAddress'
        );
        $event->setName('controller_action_postdispatch_checkout_onepage_saveShipping');
        $shippingAddress = $quote->getShippingAddress();
        Mage::app()->getRequest()->setMethod('POST')
        ->setPost('shipping', $shippingAddress->getData());
        
        $observer = $observerModel->validateAddress($observer);
        
        $result = $observer->getResult();
        $noError = ($result['error'] === false) ? true : false;
        $this->assertTrue($noError);
        $data = json_decode($result['data'],true);
        $hasData = (is_array($data) && count($data)>0) ? true : false;
        $this->assertTrue($hasData);
        
        $postcode = $data[0]['postcode'];
        $populated = ($shippingAddress->getPostcode() === $postcode) ? true : false;
        
        $this->assertTrue($populated);
        
    }
    
    /**
     *
     * @test
     * @loadFixture testModel.yaml
     */    
    public function testService()
    {
        echo "\nAydus_Addressavalidator model test completed";
        
    }
	
}