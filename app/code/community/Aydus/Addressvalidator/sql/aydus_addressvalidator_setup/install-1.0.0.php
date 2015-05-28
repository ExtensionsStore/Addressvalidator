<?php
/**
 * Address Validator setup
 *
 * @category   Aydus
 * @package    Aydus_Addressvalidator
 * @author     Aydus <davidt@aydus.com>
 */

$this->startSetup();

$this->run("CREATE TABLE IF NOT EXISTS {$this->getTable('aydus_addressvalidator_responses')} (
`hash` VARCHAR(32) NOT NULL,
`response` BLOB NOT NULL,
`response_code` VARCHAR(20) NOT NULL,
`service` VARCHAR(35) NOT NULL,
`firstname` VARCHAR(35) NOT NULL,
`lastname` VARCHAR(35) NOT NULL,
`name` VARCHAR(70) NOT NULL,
`email` VARCHAR(50) NOT NULL,
`company` VARCHAR(70) NOT NULL,
`street1` VARCHAR(95) NOT NULL,
`street2` VARCHAR(95) NOT NULL,
`city` VARCHAR(45) NOT NULL,
`region` VARCHAR(50) NOT NULL,
`postcode` VARCHAR(12) NOT NULL,
`country_id` VARCHAR(3) NOT NULL,
`telephone` VARCHAR(20) NOT NULL,
`store_id` TINYINT(3) UNSIGNED NOT NULL,
`date_created` DATETIME NOT NULL,
PRIMARY KEY ( `hash` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->endSetup();