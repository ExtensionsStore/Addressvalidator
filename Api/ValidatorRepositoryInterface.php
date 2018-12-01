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
use ExtensionsStore\Addressvalidator\Api\Data\ValidatorInterface;

interface ValidatorRepositoryInterface {
	
	public function getById($id);
	
	public function getByQuote($addressType, $customerAddressId = null);
	
	public function save(ValidatorInterface $validator);
	
	public function delete(ValidatorInterface $validator);
	
	public function getList(SearchCriteriaInterface $searchCriteria);
}