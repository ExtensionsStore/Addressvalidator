<?php

/**
 * Service sources
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_Addressvalidator_Model_Adminhtml_System_Config_Source_Service {

    public function toOptionArray() {
        $options = array();

        $options[] = array('label' => Mage::helper('adminhtml')->__('-- Please Select --'), 'value' => '');
        $options[] = array('value' => 'ups', 'label' => 'UPS');
        $options[] = array('value' => 'fedex', 'label' => 'Fedex');
        $options[] = array('value' => 'usps', 'label' => 'USPS');
        $options[] = array('value' => 'addressdoctor', 'label' => 'Address Doctor');
        $options[] = array('value' => 'melissadata', 'label' => 'Melissa Data');

        return $options;
    }

}
