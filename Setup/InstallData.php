<?php

/**
 *
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config as EavConfig ;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {
	protected $_eavSetupFactory;
	protected $_eavConfig;
	public function __construct(EavSetupFactory $eavSetupFactory, EavConfig $eavConfig) {
		$this->_eavSetupFactory = $eavSetupFactory;
		$this->_eavConfig = $eavConfig;
	}
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
		$eavSetup = $this->_eavSetupFactory->create ( [ 
				'setup' => $setup 
		] );
		$eavSetup->addAttribute ( 'customer_address', 'address_validated', [ 
				'type' => 'int',
				'label' => 'Address Validated',
				'input' => 'boolean',
				'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
				'required' => false,
				'visible' => false,
				'user_defined' => true,
				'position' => 999,
				'system' => 0 
		] );
		
		$addressValidatedAttribute = $this->_eavConfig->getAttribute('customer_address', 'address_validated');
		
		$addressValidatedAttribute->setData(
				'used_in_forms',
				['adminhtml_customer_address','customer_address_edit','customer_register_address']
				);
		$addressValidatedAttribute->save();
	}
}
