<?php 

/**
 * Helper test
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_Addressvalidator_Test_Helper_TestHelper extends EcomDev_PHPUnit_Test_Case_Config
{	
    
    /**
     *
     * @test
     * @loadFixture testHelper.yaml
     */    
    public function testHelper()
    {
        echo "\nExtensionsStore_Addressavalidator helper test started..";
        
        $helper = Mage::helper('addressvalidator');
        
        $_SERVER['HTTP_HOST'] = 'local.example.com';
        $isDebug = $helper->isDebug();
        $this->assertTrue($isDebug);
        
        echo "\nExtensionsStore_Addressavalidator helper test completed";
        
    }
	
}