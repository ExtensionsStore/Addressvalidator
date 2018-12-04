<?php

namespace ExtensionsStore\Addressvalidator\Block\Adminhtml\System\Config\Form\Field;

/**
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
class Version extends \Magento\Config\Block\System\Config\Form\Field {

	/**
	 * 
	 * @var \Magento\Framework\Module\ModuleListInterface
	 */
	protected $_moduleListInterface;
	
	/**
	 *
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Framework\Module\ModuleListInterface $moduleModuleListInterface
	 * @param array $data
	 */
	public function __construct(\Magento\Backend\Block\Template\Context $context, 
		\Magento\Framework\Module\ModuleListInterface $moduleListInterface,
		array $data = []) {
		parent::__construct ( $context, $data );
		$this->_moduleListInterface = $moduleListInterface;
	}
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Magento\Config\Block\System\Config\Form\Field::_getElementHtml()
	 */
	protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
		$element->setReadonly ( true, true );
		$moduleInfo = $this->_moduleListInterface->getOne('ExtensionsStore_Addressvalidator');
		if (isset($moduleInfo['setup_version'])){
			$version = $moduleInfo['setup_version'];
			$element->setValue ( $version );
			
		}
		
		return parent::_getElementHtml ( $element );
	}
}