<?php

/**
 * Address Validator service API
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model\Service;

abstract class AbstractService extends \Magento\Framework\Model\AbstractModel {
	
	protected $_url;
	protected $_urlSecure;
	protected $_requireApartment;
	
	protected $_directoryRegionFactory;
	protected $_logger;
	protected $_storeManager;
	protected $_soapClientFactory;
	protected $_httpClientFactory;
	protected $_encryptor;
	protected $_jsonHelper;
	protected $_scopeConfig;
	protected $_configReader;
	protected $_resultFactory;
	
	public function __construct(\Magento\Framework\Model\Context $context,
			\Magento\Framework\Registry $registry,
			\Magento\Directory\Model\RegionFactory $directoryRegionFactory,
			\Psr\Log\LoggerInterface $logger,
			\Magento\Store\Model\StoreManagerInterface $storeManager,
			\Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory,
			\Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
			\Magento\Framework\Encryption\EncryptorInterface $encryptor,
			\Magento\Framework\Json\Helper\Data $jsonHelper,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Framework\Module\Dir\Reader $configReader,
			\ExtensionsStore\Addressvalidator\Model\ResultFactory $resultFactory,
			\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
			\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
			array $data = []) {
		$this->_directoryRegionFactory = $directoryRegionFactory;
		$this->_logger = $logger;
		$this->_storeManager = $storeManager;
		$this->_soapClientFactory = $soapClientFactory;
		$this->_httpClientFactory = $httpClientFactory;
		$this->_encryptor = $encryptor;
		$this->_jsonHelper = $jsonHelper;
		$this->_scopeConfig = $scopeConfig;
		$this->_configReader = $configReader;
		$this->_resultFactory = $resultFactory;
		
		$this->_requireApartment = $this->_scopeConfig->getValue('extensions_store_addressvalidator/configuration/require_apartment',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		parent::__construct ( $context, $registry, $resource, $resourceCollection, $data );
	}
	
	/**
	 * Get results for service
	 * @return array
	 */
	abstract public function getResults();
	
	/**
	 * Get url for service
	 * @return string
	 */
	public function getUrl(){
		if ($this->_storeManager->getStore()->isFrontUrlSecure()){
			return $this->_urlSecure;
		}
		
		return $this->_url;
	}
	
	/**
	 * Create result and set its service code
	 * @return \ExtensionsStore\Addressvalidator\Model\Result
	 */
	public function createResult(){
		$result = $this->_resultFactory->create();
		$service = static::SERVICE_CODE;
		$result->setService($service);
		$serviceLabel = static::SERVICE_LABEL;
		$result->setServiceLabel($serviceLabel);
		$showService = $this->_scopeConfig->getValue('extensions_store_addressvalidator/messaging/show_service',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$result->setShowService($showService);
		return $result;
	}
	
	/**
	 * Found matches message for popup
	 * @return string
	 */
	public function getMatchesAvailableMessage(){
		$matchesAvailable = $this->_scopeConfig->getValue('extensions_store_addressvalidator/messaging/matches_available',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $matchesAvailable;
	}
	
	/**
	 * No matches found message for popup
	 * @return string
	 */
	public function getInvalidAddressMessage(){
		$invalidAddress = $this->_scopeConfig->getValue('extensions_store_addressvalidator/messaging/invalid_address',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $invalidAddress;
	}
	
	/**
	 * 
	 * @return bool
	 */
	public function getRequireApartment(){
		return $this->_requireApartment;
	}
	
	/**
	 * Get apartment required message
	 * @return string
	 */
	public function getApartmentRequiredMessage(){
		$apartmentRequiredMessage = $this->_scopeConfig->getValue('extensions_store_addressvalidator/messaging/apartment_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $apartmentRequiredMessage;
	}
	
	/**
	 * Get apartment not found message
	 * @return string
	 */
	public function getApartmentNotFoundMessage(){
		
		$apartmentNotFoundMessage = $this->_scopeConfig->getValue('extensions_store_addressvalidator/messaging/apartment_not_found', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $apartmentNotFoundMessage;
	}
	
}