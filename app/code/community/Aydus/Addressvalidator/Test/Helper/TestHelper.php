<?php 

/**
 * Helper test
 *
 * @category    Aydus
 * @package     Aydus_Addressvalidator
 * @author      Aydus <davidt@aydus.com>
 */

class Aydus_Addressvalidator_Test_Helper_TestHelper extends EcomDev_PHPUnit_Test_Case_Config
{	
    
    /**
     *
     * @test
     * @loadFixture testHelper.yaml
     */    
    public function testHelper()
    {
        echo "\nAydus_Addressavalidator helper test started..";
        
        $helper = Mage::helper('addressvalidator');
        
        $_SERVER['HTTP_HOST'] = 'local.example.com';
        $isDebug = $helper->isDebug();
        $this->assertTrue($isDebug);
        
        echo "\nAydus_Addressavalidator helper test completed";
        
    }
	
}