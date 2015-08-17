<?php
/**
 * 1.4.0 upgrade
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */

$this->startSetup();

$this->run("CREATE TABLE IF NOT EXISTS {$this->getTable('aydus_addressvalidator_addresses')} (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`address_id` INT(11) NOT NULL,
`address_type` ENUM('billing','shipping') NOT NULL,
`validated` TINYINT(1) NOT NULL,
`date_created` DATETIME NOT NULL,
`date_updated` DATETIME NOT NULL,
PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->endSetup();