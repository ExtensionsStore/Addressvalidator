<?php

/**
 * Valid codes for Address Doctor
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */
class Aydus_Addressvalidator_Model_Adminhtml_System_Config_Source_Addressdoctor_Validcodes {

    public function toOptionArray() {
        $options = array();

        $options[] = array('label' => Mage::helper('adminhtml')->__('-- Please Select --'), 'value' => '');
        $options[] = array('value' => 'V4', 'label' => 'V4');
        $options[] = array('value' => 'V3', 'label' => 'V3');
        $options[] = array('value' => 'V2', 'label' => 'V2');
        $options[] = array('value' => 'V1', 'label' => 'V1');

        $options[] = array('value' => 'C4', 'label' => 'C4');
        $options[] = array('value' => 'C3', 'label' => 'C3');
        $options[] = array('value' => 'C2', 'label' => 'C2');
        $options[] = array('value' => 'C1', 'label' => 'C1');

        $options[] = array('value' => 'I4', 'label' => 'I4');
        $options[] = array('value' => 'I3', 'label' => 'I3');
        $options[] = array('value' => 'I2', 'label' => 'I2');
        $options[] = array('value' => 'I1', 'label' => 'I1');

        return $options;
    }

}
