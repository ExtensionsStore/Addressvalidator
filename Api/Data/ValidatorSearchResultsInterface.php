<?php

/**
 * Validator search results interface
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Api\Data;
use Magento\Framework\Api\SearchResultsInterface;


interface ValidatorSearchResultsInterface extends SearchResultsInterface {
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Magento\Framework\Api\SearchResultsInterface::getItems()
	 * @return \ExtensionsStore\Addressvalidator\Api\Data\ValidatorDataInterface[]
	 */
	public function getItems();
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Magento\Framework\Api\SearchResultsInterface::setItems()
	 * @param \ExtensionsStore\Addressvalidator\Api\Data\ValidatorDataInterface[] $items
	 * @return void
	 */
	public function setItems(array $items);
	
}