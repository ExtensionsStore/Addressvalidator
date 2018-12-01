<?php

/**
 *
 * Update 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Controller\Ajax;

class Update extends \ExtensionsStore\Addressvalidator\Controller\Ajax {
	
	public function execute(){
		
		$resultJson = $this->_resultJsonFactory->create();
		$resultData = ['error'=>true, 'data'=> null];
		
		try {
			if ($data = (array)$this->getPostData('addressvalidator') ){
				
				$validatorRepository = $this->_validatorRepositoryFactory->create();
				$addressType = (isset($data['address_type']) && in_array($data['address_type'], array('billing', 'shipping'))) ? $data['address_type'] : 'shipping';
				$validator = $validatorRepository->getByQuote($addressType);
				
				if ($validator->getId()){
					
					if (isset($data['address_validation_service']) && in_array($data['address_validation_service'], array('ups','usps','fedex'))){
						$addressValidated = (isset($data['address_validated']) && $data['address_validated']) ? true : false;
						$validatorData['address_validated'] = $addressValidated;
						$validatorData['service'] = $data['address_validation_service'];
						$validator->addData($validatorData);
						$validator->updateValidator();
						$validatorRepository->save($validator);
						//@todo set customer address data
						$address = (isset($data['address'])) ? (array)$data['address'] : false;
						if ($address){
							$validator->updateCustomerAddress($address);
						}
						$resultData['error'] = false;
						$resultData['data'] = __('Validator was updated');
						$resultJson->setData($resultData);
						
					} else {
						$errorMessage = __('Invalid service.');
						$code = \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST;
						$resultData['error'] = ['code' => $code, 'message' => $errorMessage];
						$resultData['data'] = $errorMessage;
						$resultJson->setHttpResponseCode($code);
						$resultJson->setData($resultData);
					}
				} else {
					$errorMessage = __('Could not load validator.');
					$code = \Magento\Framework\Webapi\Exception::HTTP_NOT_FOUND;
					$resultData['error'] = ['code' => $code, 'message' => $errorMessage];
					$resultData['data'] = $errorMessage;
					$resultJson->setHttpResponseCode($code);
					$resultJson->setData($resultData);
				}
				
			} else {
				$errorMessage = __('No address data posted.');
				$code = \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST;
				$resultData['error'] = ['code' => $code, 'message' => $errorMessage];
				$resultData['data'] = $errorMessage;
				$resultJson->setHttpResponseCode($code);
				$resultJson->setData($resultData);
			}
		} catch(\Exception $e){
			
			$errorMessage = __($e->getMessage());
			$this->_logger->log ( \Monolog\Logger::ERROR, $errorMessage );
			$code = \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR;
			$resultData['error'] = ['code' => $code, 'message' => $errorMessage];
			$resultData['data'] = $errorMessage;
			$resultJson->setHttpResponseCode($code);
			$resultJson->setData($resultData);
		}
		return $resultJson;
	}
}
