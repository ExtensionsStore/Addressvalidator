<?php

namespace ExtensionsStore\Addressvalidator\Plugin;

/**
 * Addressvalidator payment information plugin
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
class PaymentInformationManagement {
	protected $_quoteRepository;
	protected $_orderRepository;
	protected $_validatorFactory;
	protected $_validatorRepositoryFactory;
	protected $_validatorRepository;
	
	public function __construct(\Magento\Quote\Model\QuoteRepository $quoteRepository,
			\Magento\Sales\Model\OrderRepository $orderRepository,
			\ExtensionsStore\Addressvalidator\Model\ValidatorFactory $validatorFactory,
			\ExtensionsStore\Addressvalidator\Model\ValidatorRepositoryFactory $validatorRespositoryFactory
			) {
				$this->_quoteRepository = $quoteRepository;
				$this->_orderRepository = $orderRepository;
				$this->_validatorFactory = $validatorFactory;
				$this->_validatorRepositoryFactory = $validatorRespositoryFactory;
				$this->_validatorRepository = $this->_validatorRepositoryFactory->create();
	}
	
	/**
	 *
	 *
	 *
	 * Save billing information
	 * @param \Magento\Checkout\Api\PaymentInformationManagementInterface  $subject
	 * @param int $cartId
	 * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
	 * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
	 */
	public function beforeSavePaymentInformation(
			\Magento\Checkout\Api\PaymentInformationManagementInterface $subject,
			$cartId,
			\Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
			\Magento\Quote\Api\Data\AddressInterface $billingAddress = null
			) {
				
				if ($billingAddress){
					$billingExtensionAttributes = $billingAddress->getExtensionAttributes();
					$skipValidation = $billingExtensionAttributes->getSkipValidation();
					
					if (!$skipValidation){
						$billingAddressValidated = $billingExtensionAttributes->getAddressValidated();
						if ($billingAddressValidated){
							$billingAddressValidationService = $billingExtensionAttributes->getAddressValidationService();
							$billingAddressData['address_type'] = 'billing';
							$billingAddressData['address_validated'] = $billingAddressValidated;
							$billingAddressData['service'] = $billingAddressValidationService;
							
							$billingValidator = $this->_validatorRepository->getByQuote('billing');
							
							$billingValidator->addData($billingAddressData);
							$billingValidator->updateValidator();
						}
					}
				}
	}
	
	/**
	 *
	 * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $subject
	 * @param int $orderId
	 * @param string $cartId
	 * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
	 * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
	 */
	public function afterSavePaymentInformationAndPlaceOrder(
			\Magento\Checkout\Api\PaymentInformationManagementInterface $subject,
			$orderId,
			$cartId,
			\Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
			\Magento\Quote\Api\Data\AddressInterface $billingAddress = null){
				
				$order = $this->_orderRepository->get($orderId);
				$orderShippingAddress = $order->getShippingAddress();
				$orderBillingAddress = $order->getBillingAddress();
				
				$billingValidator = $this->_validatorRepository->getByQuote('billing');
				if ($billingValidator->getId()){
					$billingAddressData['quote_address_id'] = $orderBillingAddress->getQuoteAddressId();
					$billingAddressData['order_id'] = $orderId;
					$billingAddressData['order_address_id'] = $orderBillingAddress->getId();
					$billingValidator->addData($billingAddressData);
					$billingValidator->updateValidator();
					$this->_validatorRepository->save($billingValidator);
				}
				
				$shippingValidator = $this->_validatorRepository->getByQuote('shipping');
				if ($shippingValidator->getId()){
					$shippingAddressData['quote_address_id'] = $orderShippingAddress->getQuoteAddressId();
					$shippingAddressData['order_id'] = $orderId;
					$shippingAddressData['order_address_id'] = $orderShippingAddress->getId();
					$shippingValidator->addData($shippingAddressData);
					$shippingValidator->updateValidator();
					$this->_validatorRepository->save($shippingValidator);
				}
	}
}
