<?php

/**
 * Valid exceptions for countries
 * 
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */
class Aydus_Addressvalidator_Block_Adminhtml_System_Config_Form_Validexceptions extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	
	public function __construct()
	{
		$this->addColumn('country_name', array(
				'label' => Mage::helper('addressvalidator')->__('Country Name'),
				'style' => 'width:120px',
		));
		$this->addColumn('iso3_code', array(
				'label' => Mage::helper('addressvalidator')->__('Country Code'),
				'style' => 'width:50px',
		));
		
		$this->addColumn('V4', array(
				'label' => Mage::helper('addressvalidator')->__('V4'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));
		$this->addColumn('V3', array(
				'label' => Mage::helper('addressvalidator')->__('V3'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));
		$this->addColumn('V2', array(
				'label' => Mage::helper('addressvalidator')->__('V2'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));
		$this->addColumn('V1', array(
				'label' => Mage::helper('addressvalidator')->__('V1'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));

		$this->addColumn('C4', array(
				'label' => Mage::helper('addressvalidator')->__('C4'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));
		$this->addColumn('C3', array(
				'label' => Mage::helper('addressvalidator')->__('C3'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));
		$this->addColumn('C2', array(
				'label' => Mage::helper('addressvalidator')->__('C2'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));
		$this->addColumn('C1', array(
				'label' => Mage::helper('addressvalidator')->__('C1'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));		
		
		$this->addColumn('I4', array(
				'label' => Mage::helper('addressvalidator')->__('I4'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));
		$this->addColumn('I3', array(
				'label' => Mage::helper('addressvalidator')->__('I3'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));
		$this->addColumn('I2', array(
				'label' => Mage::helper('addressvalidator')->__('I2'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));
		$this->addColumn('I1', array(
				'label' => Mage::helper('addressvalidator')->__('I1'),
				'style' => 'width:10px',
				'class' => 'checkbox',
		));		
		
		$this->_addAfter = false;
		$this->_addButtonLabel = Mage::helper('addressvalidator')->__('Add field');
		parent::__construct();
	}
	
	/**
	 * Render array cell for prototypeJS template
	 *
	 * @param string $columnName
	 * @return string
	 */
	protected function _renderCellTemplate($columnName)
	{
		if (empty($this->_columns[$columnName])) {
			throw new Exception('Wrong column name specified.');
		}
		$column     = $this->_columns[$columnName];
		$inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
	
		if ($column['renderer']) {
			return $column['renderer']->setInputName($inputName)->setColumnName($columnName)->setColumn($column)
			->toHtml();
		}
		
		$return = '';
		
	        if ($columnName == 'country_name' || $columnName == 'iso3_code'){
            $return .= '<input type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' .
                    ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
                    (isset($column['class']) ? $column['class'] : 'input-text') . '"'.
                    (isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '/>';


        } else {
            $return .= '<input id="vc-#{_id}-'.$columnName.'" type="hidden" name="' . $inputName . '" value="#{' . $columnName . '}" />' .
                    '<input type="checkbox" name="vc-#{_id}-'.$columnName.'" value="#{' . $columnName . '}" ' .
                    ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
                    (isset($column['class']) ? $column['class'] : 'input-text') . '"'.
                    (isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '/>';

        }

		return $return;
	}	
	
	/**
	 * Get element html
	 *
	 * @return string
	 */
	public function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$html = parent::_getElementHtml($element);
		$html .= $this->_getChecker();
		return $html;
	}	
	
	public function _getChecker()
	{
		$id = $this->getHtmlId();
		$elementName = $this->getElement()->getName();
		$elementName = str_replace(array('[',']'),array('\[','\]'),$elementName);
		
		$script = 
'<script type="application/javascript">
				
	//checked statuses
	$$(".checkbox").each(function(ele){
	   if( $(ele).value == "1" )
	   {
	       $(ele).checked = true;
	   }
	});				
	
	//set hidden value on click
	$$(".checkbox").invoke("observe","click",function(field) {

		var hiddenId = this.name;
		
		var hiddenVal = (this.checked) ? 1 : 0;	
			
		$(hiddenId).value = hiddenVal;
			
	});				
					
</script>';
		
		
		return $script;
	}
	
	/**
	 * Generate the default field array for Jordan, SA and UAE
	 * 
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	protected function _getElementHtml2(Varien_Data_Form_Element_Abstract $element)
	{
		$countriesCollection = Mage::getResourceModel('directory/country_collection');
		$countriesCollection->addFieldToFilter('country_id',array('in'=>array('JO','NZ','SA','AE')));
		
		$countries = array();
		
		foreach ($countriesCollection as $country) {
			$name = Mage::app()->getLocale()->getCountryTranslation($country->getId());
			if (!empty($name)) {
				$countries[$name] = $country;
			}
		}
		
		Mage::helper('core/string')->ksortMultibyte($countries);		
		
		$rows = array();
		
		foreach ($countries as $name=>$country){
			$rows[] = array(
				'country_name'=>$name, 
				'iso3_code'=>$country->getIso3Code(), 
				'V4' => 1,
				'V3' => 1,
				'V2' => 1,
				'V1' => 0,
				'C4' => 1,
				'C3' => 1,
				'C2' => 1,
				'C1' => 0,
				'I4' => 1,
				'I3' => 1,
				'I2' => 0,
				'I1' => 0,
			);
		}
		
		echo serialize($rows);

		exit();
		
		return '';
	}
}
