<?php

/**
 * Validator model
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use ExtensionsStore\Addressvalidator\Api\ValidatorInterface;
use ExtensionsStore\Addressvalidator\Api\Data\ValidatorDataInterface;

class Validator extends AbstractExtensibleModel implements  ValidatorInterface, ValidatorDataInterface {
	protected $_directoryRegionFactory;
	protected $_storeManager;
	protected $_logger;
	protected $_scopeConfig;
	protected $_dateTime;
	protected $_dataObjectFactory;
	protected $_checkoutSession;
	protected $_customerSession;
	protected $_customerAddressRepository;
	protected $_serviceModel;
	protected $_request;
	protected $_errorFields;
		
	const CACHE_TAG = 'extensions_store_addressvalidator';
	
	public function __construct(\Magento\Framework\Model\Context $context, 
			\Magento\Framework\Registry $registry, 
			\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
			\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
			\Magento\Directory\Model\RegionFactory $directoryRegionFactory, 
			\Magento\Store\Model\StoreManagerInterface $storeManager, 
			\Psr\Log\LoggerInterface $logger, 
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
			\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
			\Magento\Framework\DataObjectFactory $dataObjectFactory, 
			\Magento\Checkout\Model\Session $checkoutSession, 
			\Magento\Customer\Model\Session $customerSession, 
			\Magento\Customer\Api\AddressRepositoryInterface $customerAddressRepository,
			\ExtensionsStore\Addressvalidator\Model\Service $serviceModel,
			\ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator $resource = null, 
			\ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator\Collection $resourceCollection = null, 
			array $data = []) {
		$this->_directoryRegionFactory = $directoryRegionFactory;
		$this->_storeManager = $storeManager;
		$this->_logger = $logger;
		$this->_scopeConfig = $scopeConfig;
		$this->_dateTime = $dateTime;
		$this->_dataObjectFactory = $dataObjectFactory;
		$this->_checkoutSession = $checkoutSession;
		$this->_customerSession = $customerSession;
		$this->_customerAddressRepository = $customerAddressRepository;
		$this->_serviceModel = $serviceModel;
		parent::__construct ( $context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data );
		$quote = $this->_checkoutSession->getQuote();
		$this->setQuoteId($quote->getId());
	}
	
	protected function _construct() {
		$this->_init ( \ExtensionsStore\Addressvalidator\Model\ResourceModel\Validator::class );
	}
	
	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}
		
	/**
	 * 
	 * @return \Magento\Quote\Model\Quote
	 */
	public function getQuote(){
		$quote = $this->_checkoutSession->getQuote();
		$quoteId = $quote->getId();
		$this->setQuoteId($quoteId);
		return $quote;
	}
	
	/**
	 * 
	 * Set request data for service calls
	 * @param [] $data
	 * @return \ExtensionsStore\Addressvalidator\Model\Validator
	 */
	public function setRequest($data){
		$quote = $this->getQuote();
		$this->updateValidator();
		$this->addData($data);
		$this->setStreet(implode(PHP_EOL,$data['street']));

		if (isset($data['address_type'])){
			$addressType = 'shipping';
			if ($data['address_type'] == 'billing'){
				$addressType = 'billing';
				$address = $quote->getBillingAddress();
			} else {
				$address = $quote->getShippingAddress();
			}
			$this->setAddressType($addressType);
			$this->setQuoteAddressId($address->getId());
			$customerAddressId = $address->getCustomerAddressId();
			if (!$customerAddressId && isset($data['customer_address_id']) && $data['customer_address_id']>0){
				$customerAddressId = $data['customer_address_id'];
				$customerAddress = $this->_customerAddressRepository->getById($customerAddressId);
				$addressValidated = $customerAddress->getCustomAttribute('address_validated');
				$customerAddress->setCustomAttribute('address_validated', false);
			}
			$this->setCustomerAddressId($customerAddressId);
		}
		if (isset($data['extension_attributes'])){
			$extensionAttributes = $data['extension_attributes'];
			if (isset($extensionAttributes->address_validation_service)){
				$addressValidationService = $extensionAttributes->address_validation_service;
				$this->setService($addressValidationService);
			}
			//@todo could be a customer address
			if (isset($extensionAttributes->address_validated)){
				$addressValidated = $extensionAttributes->address_validated;
				$this->setAddressValidated($addressValidated);
			}
			if (isset($extensionAttributes->skip_validation)){
				$skipValidation = $extensionAttributes->skip_validation;
				$this->setSkipValidation($skipValidation);
			}
		}
				
		$this->_request = $data;
		return $this;
	}
	
	/**
	 * 
	 * @return \ExtensionsStore\Addressvalidator\Model\Validator
	 */
	public function updateValidator(){
		$quote = $this->getQuote();
		$quoteId = $quote->getId();
		$this->setQuoteId($quoteId);
		$address = ($this->getAddressType() == 'billing') ? $quote->getBillingAddress() : $quote->getShippingAddress();
		$this->setQuoteAddressId($address->getId());
		
		$storeId = $this->_storeManager->getStore()->getId();
		$this->setStoreId($storeId);
		
		$date = $this->_dateTime->gmtDate();
		if (!$this->getId()){
			$this->setDateCreated($date);
		}
		$this->setDateUpdated($date);
		
		return $this;
	}
	
	/**
	 * 
	 * @todo refactor or remove
	 * @param [] $address
	 */
	public function updateCustomerAddress($address){
		$customerAddressId = (isset($address['customer_address_id'])) ? $address['customer_address_id'] : false;
		
		if ($customerAddressId){
			$customerAddress = $this->_customerAddressRepository->getById($customerAddressId);
			if ($customerAddress->getId()){
				
				$customerAddress->setCustomAttribute('address_validated', true);
				
				$street = @$address['street'];
				if ($street){
					$customerAddress->setStreet($street);
				}
				$city = @$address['city'];
				if ($city){
					$customerAddress->setCity($city);
				}
				//@todo set region
				$regionId = @$address['region_id'];
				if ($regionId){
					$customerAddress->setRegionId($regionId);
				}
				$postcode = @$address['postcode'];
				if ($postcode){
					$customerAddress->setPostcode($postcode);
				}
				$countryId = @$address['countryId'];
				if ($countryId){
					$customerAddress->setCountryId($countryId);
				}
				
				$this->_customerAddressRepository->save($customerAddress);
			}
		}
		
		return $this;
	}
	
	/**
	 * 
	 * @return array|string String is error message
	 */
	public function getResults() {
		
		$resultObjs = [];
		$services = [];
		
		$service = $this->_scopeConfig->getValue('extensions_store_addressvalidator/configuration/service', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$services[] = $service;
		$service2 = $this->_scopeConfig->getValue('extensions_store_addressvalidator/configuration/service2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($service2){
			$services[] = $service2;
		}
		$this->setService(implode(',',$services));
		foreach ($services as $serviceCode){
			if (in_array($serviceCode, $this->_serviceModel->getServiceCodes())){
				$serviceModel = $this->_serviceModel->getServiceModel($serviceCode);
				$serviceModel->setData($this->_request);
				$results = $serviceModel->getResults($this->_request);
				foreach ($results as $resultObj){
					if ($resultObj->getIsCommercial()){
						if (!$this->_getData('company')){
							$this->_errorFields[] = 'company';
						}
						$this->setIsCommercial(true);
					}
					if ($resultObj->getApartmentRequired()){
						$this->setApartmentRequired(true);
						$this->_errorFields[] = 'street[1]';
					}
					//if main service returns error, return error messae
					if ($serviceCode == $service && $resultObj->getError()){
						return $resultObj->getErrorMessage();
					}
				}
				$resultObjs[$serviceCode] = $results;
			}
		}
				
		return $resultObjs;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \ExtensionsStore\Addressvalidator\Api\ValidatorInterface::getErrorFields()
	 */
	public function getErrorFields(){
		return $this->_errorFields;
	}
	
	/*
	 * Getter and setters
	 */
	
	/**
	 * @return string
	 */
	public function getAddressType(){
		return $this->getData('address_type');
	}
	
	/**
	 *
	 * @param string $addressType
	 * @return $this
	 */
	public function setAddressType($addressType){
		$this->setData('address_type', $addressType);
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getQuoteId(){
		return $this->getData('quote_id');
	}
	
	/**
	 * @param int $quoteId
	 * @return $this
	 */
	public function setQuoteId($quoteId){
		$this->setData('quote_id', $quoteId);
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getQuoteAddressId(){
		return $this->getData('quote_address_id');
	}
	
	/**
	 * @param int $quoteAddressId
	 * @return $this
	 */
	public function setQuoteAddressId($quoteAddressId){
		$this->setData('quote_address_id', $quoteAddressId);
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getOrderId(){
		return $this->getData('order_id');
	}
	
	/**
	 * @param int $orderId
	 * @return $this
	 */
	public function setOrderId($orderId){
		$this->setData('order_id', $orderId);
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getOrderAddressId(){
		return $this->getData('order__address_id');
	}
	
	/**
	 * @param int $orderAddressId
	 * @return $this
	 */
	public function setOrderAddressId($orderAddressId){
		$this->setData('order_address_id', $orderAddressId);
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getCustomerAddressId(){
		return $this->getData('customer_address_id');
	}
	
	/**
	 * @param int $customerAddressId
	 * @return $this
	 */
	public function setCustomerAddressId($customerAddressId){
		$this->setData('customer_address_id', $customerAddressId);
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getStoreId(){
		return $this->getData('store_id');
	}
	
	/**
	 * @param int $storeId
	 * @return $this
	 */
	public function setStoreId($storeId){
		$this->setData('store_id', $storeId);
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function getAddressValidated(){
		return $this->getData('address_validated');
	}
	
	/**
	 * @param bool $addressValidated
	 * @return $this
	 */
	public function setAddressValidated($addressValidated){
		$this->setData('address_validated', $addressValidated);
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function getSkipValidation(){
		return $this->getData('skip_validation');
	}
	
	/**
	 * @param bool $skipValidation
	 * @return $this
	 */
	public function setSkipValidation($skipValidation){
		$this->setData('skip_validation', $skipValidation);
		return $this;
	}
	
}