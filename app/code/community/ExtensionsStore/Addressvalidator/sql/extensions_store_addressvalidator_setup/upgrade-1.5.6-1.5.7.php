<?php
/**
 * 1.5.7 upgrade
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */

$this->startSetup();

$responsesTable = $this->getTable('addressvalidator/response');

$this->getConnection()
->addColumn($responsesTable,'quote_address_id', array(
		'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'nullable'  => false,
		'length'    => 11,
		'after'     => 'quote_id',
		'comment'   => 'Quote Address ID'
));

$this->endSetup();