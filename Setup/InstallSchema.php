<?php

/**
 * 
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface {

	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$installer = $setup;
		$installer->startSetup ();
		
		//log of validation requests
		$table = $installer->getConnection ()->newTable ( $installer->getTable ( 'extensions_store_addressvalidator' ) )
		->addColumn ( 'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [
				'identity' => true,
				'unsigned' => true,
				'nullable' => false,
				'primary' => true
		], 'Address Validator Address ID' )
		->addColumn ( 'service', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 35, [
				'nullable' => true,
		], 'The type of service Used, i.e. USPS, FedEx, UPS, etc.' )
		->addColumn ( 'address_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10, [
				'nullable' => true,
		], 'Billing or shipping' )
		->addColumn ( 'firstname', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
				'nullable' => true,
		], 'Quote Address First Name' )
		->addColumn ( 'lastname', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
				'nullable' => true,
		], 'Quote Address Last Name' )
		->addColumn ( 'email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
				'nullable' => true,
		], 'Quote Address Email' )
		->addColumn ( 'company', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
				'nullable' => true,
		], 'Quote Address Company' )
		->addColumn ( 'street', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
				'nullable' => true,
		], 'Quote Address Street' )
		->addColumn ( 'city', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
				'nullable' => true,
		], 'Quote Address City' )
		->addColumn ( 'region', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
				'nullable' => true,
		], 'Quote Address Region/State' )
		->addColumn ( 'region_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [
				'nullable' => true,
		], 'Quote Address Region ID' )
		->addColumn ( 'postcode', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [
				'nullable' => true,
		], 'Quote Address Postcode/Zip' )
		->addColumn ( 'country_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 3, [
				'nullable' => true,
		], 'Quote Address Country ID' )
		->addColumn ( 'telephone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [
				'nullable' => true,
		], 'Quote Address Country ID' )
		->addColumn ( 'quote_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [
				'nullable' => true,
				'unsigned' => true,
		], 'Quote ID' )
		->addColumn ( 'quote_address_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [
				'nullable' => true,
				'unsigned' => true,
		], 'Quote Address ID' )
		->addColumn ( 'order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [
				'nullable' => true,
				'unsigned' => true,
		], 'Order ID' )
		->addColumn ( 'order_address_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [
				'nullable' => true,
				'unsigned' => true,
		], 'Order Address ID' )
		->addColumn ( 'customer_address_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [
				'nullable' => true,
				'unsigned' => true,
		], 'Customer Address ID (from quote address)' )
		->addColumn ( 'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5, [
				'nullable' => false,
				'unsigned' => true,
		], 'Store ID' )
		->addColumn ( 'address_validated', \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, null, [
				'nullable' => false,
				'default' => false,
		], 'Address was already validated ' )
		->addColumn ( 'skip_validation', \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, null, [
				'nullable' => false,
				'default' => false,
		], 'Skip all validation for this customer' )
		->addColumn ( 'apartment_required', \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, null, [
				'nullable' => false,
				'default' => false,
		], 'Apartment is required for this address' )
		->addColumn ( 'is_commercial', \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, null, [
				'nullable' => false,
				'default' => false,
		], 'Commercial or business address' )
		->addColumn ( 'date_created', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [
				'nullable' => false,
				'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
		], 'Creation Time' )
		->addColumn ( 'date_updated', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [
				'nullable' => false,
				'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
		], 'Update Time' )
		->addIndex(
				$installer->getIdxName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						['service'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
						),
				['service'],
				['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
				)
		->addIndex(
				$installer->getIdxName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						['region_id'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
						),
				['region_id'],
				['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
				)
		->addIndex(
				$installer->getIdxName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						['quote_id'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
						),
				['quote_id'],
				['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
				)
		->addForeignKey(
				$installer->getFkName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						'quote_id',
						$installer->getTable ( 'quote' ),
						'entity_id'
						),
				'quote_id',
				$installer->getTable ( 'quote' ),
				'entity_id',
				\Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
				)
		->addForeignKey(
				$installer->getFkName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						'quote_address_id',
						$installer->getTable ( 'quote_address' ),
						'address_id'
						),
				'quote_address_id',
				$installer->getTable ( 'quote_address' ),
				'address_id',
				\Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
				)
		->addIndex(
				$installer->getIdxName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						['order_id'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
						),
				['order_id'],
				['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
				)
		->addForeignKey(
				$installer->getFkName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						'order_id',
						$installer->getTable ( 'sales_order' ),
						'entity_id'
						),
				'order_id',
				$installer->getTable ( 'sales_order' ),
				'entity_id',
				\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
				)
		->addForeignKey(
				$installer->getFkName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						'order_address_id',
						$installer->getTable ( 'sales_order_address' ),
						'entity_id'
						),
				'order_address_id',
				$installer->getTable ( 'sales_order_address' ),
				'entity_id',
				\Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
				)
		->addIndex(
				$installer->getIdxName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						['store_id'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
						),
				['store_id'],
				['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
				)
		->addForeignKey(
				$installer->getFkName(
						$installer->getTable ( 'extensions_store_addressvalidator' ),
						'store_id',
						$installer->getTable ( 'store' ),
						'store_id'
						),
				'store_id',
				$installer->getTable ( 'store' ),
				'store_id',
				\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
				);
				
		$installer->getConnection ()->createTable ( $table );
		
		$installer->endSetup ();
	}
}
