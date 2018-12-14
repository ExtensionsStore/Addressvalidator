<?php

/**
 * Validator repository interface
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use ExtensionsStore\Addressvalidator\Api\Data\ValidatorDataInterface;

interface ValidatorRepositoryInterface {
	
	/**
	 * Get by validator entity id
	 * @param int $id
	 * @return \ExtensionsStore\Addressvalidator\Api\ValidatorInterface
	 */
	public function getById($id);
	
	/**
	 * Get quote
	 * @param string $addressType
	 * @param number $customerAddressId
	 * @return \ExtensionsStore\Addressvalidator\Api\ValidatorInterface
	 */
	public function getByQuote($addressType, $customerAddressId = null);
	
	public function save(ValidatorDataInterface $validator);
	
	public function delete(ValidatorDataInterface $validator);
	
	public function getList(SearchCriteriaInterface $searchCriteria);
}