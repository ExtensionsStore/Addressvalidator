<?php

/**
 * Validator repository
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use ExtensionsStore\Addressvalidator\Api\ValidatorRepositoryInterface;
use ExtensionsStore\Addressvalidator\Api\Data\ValidatorDataInterface;
use ExtensionsStore\Addressvalidator\Api\Data\ValidatorSearchResultsInterface;
use ExtensionsStore\Addressvalidator\Api\Data\ValidatorSearchResultsInterfaceFactory;
use ExtensionsStore\Addressvalidator\Model\Validator;
use ExtensionsStore\Addressvalidator\Model\ValidatorFactory;
use ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator as ValidatorResource;
use ExtensionsStore\Addressvalidator\Model\ResourceModel\ValidatorFactory as ValidatorResourceFactory;
use ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator\Collection;
use ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator\CollectionFactory as validatorCollectionFactory;

class ValidatorRepository implements ValidatorRepositoryInterface {
	/**
	 *
	 * @var \ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator
	 */
	protected $_validatorFactory;
	
	/**
	 *
	 * @var \ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator\Collection
	 */
	protected $_validatorCollectionFactory;
	
	/**
	 * 
	 * @var array
	 */
	protected $_validators = ['billing'=>null, 'shipping'=>null];
	
	/**
	 *
	 * @var \ExtensionsStore\Addressvalidator\Api\Data\ValidatorSearchResultsInterface
	 */
	protected $_searchResultFactory;
	public function __construct(validatorFactory $validatorFactory, 
			validatorCollectionFactory $validatorCollectionFactory, 
			ValidatorSearchResultsInterfaceFactory $validatorSearchResultsInterfaceFactory) {
		$this->_validatorFactory = $validatorFactory;
		$this->_validatorCollectionFactory = $validatorCollectionFactory;
		$this->_searchResultFactory = $validatorSearchResultsInterfaceFactory;
	}
	public function getList(SearchCriteriaInterface $searchCriteria) {
		$collection = $this->_validatorCollectionFactory->create ();
		
		$this->addFiltersToCollection ( $searchCriteria, $collection );
		$this->addSortOrdersToCollection ( $searchCriteria, $collection );
		$this->addPagingToCollection ( $searchCriteria, $collection );
		
		$collection->load ();
		
		return $this->buildSearchResult ( $searchCriteria, $collection );
	}
	private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection) {
		foreach ( $searchCriteria->getFilterGroups () as $filterGroup ) {
			$fields = $conditions = [ ];
			foreach ( $filterGroup->getFilters () as $filter ) {
				$fields [] = $filter->getField ();
				$conditions [] = [ 
						$filter->getConditionType () => $filter->getValue () 
				];
			}
			$collection->addFieldToFilter ( $fields, $conditions );
		}
	}
	private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection) {
		foreach ( ( array ) $searchCriteria->getSortOrders () as $sortOrder ) {
			$direction = $sortOrder->getDirection () == SortOrder::SORT_ASC ? 'asc' : 'desc';
			$collection->addOrder ( $sortOrder->getField (), $direction );
		}
	}
	private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection) {
		$collection->setPageSize ( $searchCriteria->getPageSize () );
		$collection->setCurPage ( $searchCriteria->getCurrentPage () );
	}
	private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection) {
		$searchResults = $this->_searchResultFactory->create ();
		
		$searchResults->setSearchCriteria ( $searchCriteria );
		$searchResults->setItems ( $collection->getItems () );
		$searchResults->setTotalCount ( $collection->getSize () );
		
		return $searchResults;
	}
	/**
	 * 
	 * {@inheritDoc}
	 * @see \ExtensionsStore\Addressvalidator\Api\ValidatorRepositoryInterface::getById()
	 */
	public function getById($id) {
		$validator = $this->_validatorFactory->create();
		$validator->getResource()->load($validator, $id);
		if (! $validator->getId()) {
			throw new NoSuchEntityException(__('Unable to find validator with ID "%1"', $id));
		}
		return $validator;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \ExtensionsStore\Addressvalidator\Api\ValidatorRepositoryInterface::getByQuoteId()
	 */
	public function getByQuote($addressType, $customerAddressId = null) {
		if (!$this->_validators[$addressType]){
			$validator = $this->_validatorFactory->create();
			$quoteId = $validator->getQuote()->getId();
			$validator->setQuoteId($quoteId);
			$validator->setAddressType($addressType);
			$validators = $this->_validatorCollectionFactory->create();
			$validators->addFieldToFilter('address_type', $addressType);
			if ($customerAddressId){
				$validator->setCustomerAddressId($customerAddressId);
				$validators->addFieldToFilter('quote_id', $quoteId);
				$validators->addFieldToFilter('customer_address_id', $customerAddressId);
			} else {
				$validators->addFieldToFilter('quote_id', $quoteId);
			}
			$validators->addFieldToFilter('order_id', ['null'=>true]);
			$sql = (string)$validators->getSelect();
			if ($validators->getSize()>0){
				$validator = $validators->getFirstItem();
			}
			$this->_validators[$addressType] = $validator;
		}

		return $this->_validators[$addressType];
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \ExtensionsStore\Addressvalidator\Api\ValidatorRepositoryInterface::save()
	 */
	public function save(ValidatorDataInterface $validatorData) {
		$validatorData->getResource()->save($validatorData);
		return $validatorData;
	}
	/**
	 * 
	 * {@inheritDoc}
	 * @see \ExtensionsStore\Addressvalidator\Api\ValidatorRepositoryInterface::delete()
	 */
	public function delete(ValidatorDataInterface $validatorData) {
		$validatorData->getResource()->delete($validatorData);
	}
}
