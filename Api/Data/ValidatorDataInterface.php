<?php

/**
 * Validator data interface
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Api\Data;
use Magento\Framework\Api\ExtensibleDataInterface;


interface ValidatorDataInterface extends ExtensibleDataInterface {
	
	/**
	 * @return int
	 */
	public function getId();
	
	/**
	 * @param int $id
	 * @return $this
	 */
	public function setId($id);
	
	/**
	 * @return string
	 */
	public function getAddressType();
	
	/**
	 *
	 * @param string $addressType
	 * @return $this
	 */
	public function setAddressType($addressType);
	
	/**
	 * @return int
	 */
	public function getQuoteId();
	
	/**
	 * @param int $quoteId
	 * @return $this
	 */
	public function setQuoteId($quoteId);
	
	/**
	 * @return int
	 */
	public function getQuoteAddressId();
	
	/**
	 * @param int $quoteAddressId
	 * @return $this
	 */
	public function setQuoteAddressId($quoteAddressId);
	
	/**
	 * @return int
	 */
	public function getOrderId();
	
	/**
	 * @param int $orderId
	 * @return $this
	 */
	public function setOrderId($orderId);
	
	/**
	 * @return int
	 */
	public function getOrderAddressId();
	
	/**
	 * @param int $orderAddressId
	 * @return $this
	 */
	public function setOrderAddressId($orderAddressId);
	
	/**
	 * @return int
	 */
	public function getCustomerAddressId();
	
	/**
	 * @param int $customerAddressId
	 * @return $this
	 */
	public function setCustomerAddressId($customerAddressId);
	
	/**
	 * @return int
	 */
	public function getStoreId();
	
	/**
	 * @param int $storeId
	 * @return $this
	 */
	public function setStoreId($storeId);
	
	/**
	 * @return bool
	 */
	public function getAddressValidated();
	
	/**
	 * @param bool $addressValidated
	 * @return $this
	 */
	public function setAddressValidated($addressValidated);
	
	/**
	 * @return bool
	 */
	public function getSkipValidation();
	
	/**
	 * @param bool $skipValidation
	 * @return $this
	 */
	public function setSkipValidation($skipValidation);
	
}