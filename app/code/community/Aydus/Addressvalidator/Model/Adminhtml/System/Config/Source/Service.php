<?php

/** 
 * Service sources
 *
 * @category   Aydus
 * @package	   Aydus_Addressvalidator
 * @author     Aydus Consulting <davidt@aydus.com>
 */

class Aydus_Addressvalidator_Model_Adminhtml_System_Config_Source_Service
{
	public function toOptionArray()
	{
		$options = array();
		
        $options[] = array('label' => Mage::helper('adminhtml')->__('-- Please Select --'), 'value' => '');
		$options[] = array('value' => 'usps', 'label' => 'USPS');
        $options[] = array('value' => 'addressdoctor', 'label' => 'Address Doctor');
		$options[] = array('value' => 'melissadata', 'label' => 'Melissa Data');
		
		return $options;
	}
}
