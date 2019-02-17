<?php

namespace ExtensionsStore\Addressvalidator\Plugin;

/**
 * Addressvalidator customer address plugin
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
class CustomerAddress {
	protected $_validatorFactory;
	protected $_validatorRepositoryFactory;
	protected $_validatorRepository;
	protected $_searchCriteriaBuilder;
	protected $_extensionAttributesFactory;
	protected $_scopeConfig;
	protected $_enabled;
	
	public function __construct(\ExtensionsStore\Addressvalidator\Model\ValidatorFactory $validatorFactory,
			\ExtensionsStore\Addressvalidator\Model\ValidatorRepositoryFactory $validatorRespositoryFactory,
			\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
			\Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
			) {
				$this->_validatorFactory = $validatorFactory;
				$this->_validatorRepositoryFactory = $validatorRespositoryFactory;
				$this->_validatorRepository = $this->_validatorRepositoryFactory->create();
				$this->_searchCriteriaBuilder = $searchCriteriaBuilder;
				$this->_extensionAttributesFactory = $extensionAttributesFactory;
				$this->_scopeConfig = $scopeConfig;
				$this->_enabled = $this->_scopeConfig->getValue('extensions_store_addressvalidator/configuration/enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 *
	 * @param \Magento\Customer\Api\AddressRepositoryInterface $subject
	 * @param \Magento\Customer\Api\Data\AddressInterface $customerAddress
	 * @return \Magento\Customer\Api\Data\AddressInterface
	 */
	public function afterGetById(\Magento\Customer\Api\AddressRepositoryInterface $subject, \Magento\Customer\Api\Data\AddressInterface $customerAddress){
		if ($this->_enabled){
			$extensionAttributes = $customerAddress->getExtensionAttributes();
			if (!$extensionAttributes){
				$extensionAttributes = $this->_extensionAttributesFactory->create('Magento\Customer\Api\Data\AddressInterface');
				$searchCriteria = $this->_searchCriteriaBuilder->addFilter('customer_address_id', $customerAddress->getId(), 'eq')->create();
				$validatorList = $this->_validatorRepository->getList($searchCriteria);
				if ($validatorList->getTotalCount()>0){
					$items = $validatorList->getItems();
					$validator = reset($items);
					$addressValidated = $validator->getAddressValidated();
					$extensionAttributes->setAddressValidated($addressValidated);
				}
				$customerAddress->setExtensionAttributes($extensionAttributes);
			}
		}
		return $customerAddress;
	}
	
}
