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
	
	public function __construct(\ExtensionsStore\Addressvalidator\Model\ValidatorFactory $validatorFactory,
			\ExtensionsStore\Addressvalidator\Model\ValidatorRepositoryFactory $validatorRespositoryFactory
			) {
		$this->_validatorFactory = $validatorFactory;
		$this->_validatorRepositoryFactory = $validatorRespositoryFactory;
		$this->_validatorRepository = $this->_validatorRepositoryFactory->create();
	}
	
	public function beforeSave(\Magento\Customer\Api\AddressRepositoryInterface $subject, \Magento\Customer\Api\Data\AddressInterface $customerAddress){
		
		
		
		return $customerAddress;
	}

}
