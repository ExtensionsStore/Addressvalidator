<?php

namespace ExtensionsStore\Addressvalidator\Plugin;

/**
 * Addressvalidator shipping information plugin
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
class ShippingInformationManagement {
	protected $_quoteRepository;
	protected $_validatorFactory;
	protected $_validatorRepositoryFactory;
	
	public function __construct(\Magento\Quote\Model\QuoteRepository $quoteRepository, 
			\ExtensionsStore\Addressvalidator\Model\ValidatorFactory $validatorFactory,
			\ExtensionsStore\Addressvalidator\Model\ValidatorRepositoryFactory $validatorRespositoryFactory
			) {
		$this->_quoteRepository = $quoteRepository;
		$this->_validatorFactory = $validatorFactory;
		$this->_validatorRepositoryFactory = $validatorRespositoryFactory;
	}
	
	/**
	 * Update validation data
	 * @param \Magento\Quote\Model\ShippingMethodManagement $subject
	 * @param $cartId
	 * @param \Magento\Quote\Model\Quote\Address $address
	 */
	public function beforeSaveAddressInformation(\Magento\Checkout\Api\ShippingInformationManagementInterface $subject, 
			$cartId, \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation) {
								
			$billingAddress = $addressInformation->getBillingAddress();
			$shippingAddress = $addressInformation->getShippingAddress();
			$billingExtensionAttributes = $billingAddress->getExtensionAttributes();
			$shippingExtensionAttributes = $shippingAddress->getExtensionAttributes();
			$skipValidation = $billingExtensionAttributes->getSkipValidation() || $shippingExtensionAttributes->getSkipValidation();
			
			if (!$skipValidation){
				$validatorRepository = $this->_validatorRepositoryFactory->create();
				$billingValidator = $validatorRepository->getByQuote('billing');
				$shippingValidator = $validatorRepository->getByQuote('shipping');
				
				$billingAddressValidated = $billingExtensionAttributes->getAddressValidated();
				$shippingAddressValidated = $shippingExtensionAttributes->getAddressValidated();
				$billingAddressValidationService = $billingExtensionAttributes->getAddressValidationService();
				$shippingAddressValidationService = $shippingExtensionAttributes->getAddressValidationService();
				
				if (!$billingValidator->getId()){
					$billingAddressData = $shippingValidator->getData();
					unset($billingAddressData['id']);
					unset($billingAddressData['quote_address_id']);
					unset($billingAddressData['order_address_id']);
				}
				$billingAddressData['address_type'] = 'billing';
				$billingAddressData['address_validated'] = $billingAddressValidated;
				$billingAddressData['service'] = $billingAddressValidationService;
				
				$shippingAddressData['address_type'] = 'shipping';
				$shippingAddressData['address_validated'] = $shippingAddressValidated;
				$shippingAddressData['service'] = $shippingAddressValidationService;
								
				$billingValidator->addData($billingAddressData);
				$billingValidator->updateValidator();
				$validatorRepository->save($billingValidator);
				$shippingValidator->addData($shippingAddressData);
				$shippingValidator->updateValidator();
				$validatorRepository->save($shippingValidator);
			}

	}
}
