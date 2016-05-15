<?php
/**
 * 1.5.0 upgrade
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Addressvalidator
 * @author     Extensions Store <admin@extensions-store.com>
 */

$this->startSetup();

$oldAddressesTable = $this->getTable('aydus_addressvalidator_addresses');
$newAddressesTable = $this->getTable('extensions_store_addressvalidator_addresses');
$oldResponsesTable = $this->getTable('aydus_addressvalidator_responses');
$newResponsesTable = $this->getTable('extensions_store_addressvalidator_responses');
$connection = $this->getConnection();

if ($connection->isTableExists($oldAddressesTable)){

	if (!$connection->isTableExists($newAddressesTable)){
		$sql = "CREATE TABLE {$newAddressesTable} LIKE {$oldAddressesTable}";
		$this->run($sql);
	}
	$sql = "INSERT {$newAddressesTable} SELECT * FROM {$oldAddressesTable}";
	$this->run($sql);
	$sql = "DROP TABLE {$oldAddressesTable}";
	$this->run($sql);
}

if ($connection->isTableExists($oldResponsesTable)){
	
	if (!$connection->isTableExists($newResponsesTable)){
		$sql = "CREATE TABLE {$newResponsesTable} LIKE {$oldResponsesTable}";
		$this->run($sql);
	}
	$sql = "INSERT {$newResponsesTable} SELECT * FROM {$oldResponsesTable}";
	$this->run($sql);
	$sql = "DROP TABLE {$oldResponsesTable}";
	$this->run($sql);
}

$coreResource = $this->getTable('core_resource');
$sql = "DELETE FROM {$coreResource} WHERE code = 'aydus_addressvalidator_setup' AND version ='1.4.3' AND data_version = '1.4.3'";
$this->run($sql);

$coreConfigData = $this->getTable('core_config_data');
$sql = "UPDATE $coreConfigData SET path = REPLACE(path, 'aydus', 'extensions_store') WHERE path like 'aydus_addressvalidator%'";

$this->endSetup();