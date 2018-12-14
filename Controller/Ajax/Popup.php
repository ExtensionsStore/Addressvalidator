<?php

/**
 * 
 * Ajax popup
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Controller\Ajax;

class Popup extends \ExtensionsStore\Addressvalidator\Controller\Ajax {
	
	public function execute(){
				
		$resultJson = $this->_resultJsonFactory->create();
		$resultData = ['error'=>true, 'data'=> null];
		
		try {
			if ($data = (array)$this->getPostData('addressvalidator') ){
				
				if (isset($data['extension_attributes']) || isset($data['custom_attributes'])){
					$extensionAttributes = $data['extension_attributes'];
					$customAttributes = $data['custom_attributes'];
					//forbidden request
					if ((isset($customAttributes->address_validated) && $customAttributes->address_validated) ||
						(isset($extensionAttributes->address_validated) && $extensionAttributes->address_validated) || 
						(isset($extensionAttributes->skip_validation) && $extensionAttributes->skip_validation)	){
							$errorMessage = __('Invalid request.');
							$this->_logger->log ( \Monolog\Logger::ERROR, $errorMessage );
							$code = \Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN;
							$resultData['error'] = ['code' => $code, 'message' => $errorMessage];
							$resultData['data'] = $errorMessage;
							$resultJson->setHttpResponseCode($code);
							$resultJson->setData($resultData);
							return $resultJson;
					}
				}
				
				$validatorRepository = $this->_validatorRepositoryFactory->create();
				$addressType = (isset($data['address_type']) && in_array($data['address_type'], array('billing', 'shipping'))) ? $data['address_type'] : 'shipping';
				$customerAddressId = (isset($data['customer_address_id']) && $data['customer_address_id']) ? $data['customer_address_id'] : null;
				$validator = $validatorRepository->getByQuote($addressType, $customerAddressId);
				$resultObjs = false;
				$extensionAttributes = (isset($data['extension_attributes'])) ? $data['extension_attributes'] : null;
				$addressValidated = ($extensionAttributes && isset($extensionAttributes->address_validated)) ? $extensionAttributes->address_validated : null;
				if (!is_null($addressValidated) || !$validator->getAddressValidated()){
					$validator->setRequest($data);
					$resultObjs = $validator->getResults();
					if ($resultObjs){
						$validatorRepository->save($validator);
					}
				}

				$resultsAr = [];
				
				$layout = $this->_layoutFactory->create();
				$popup = $layout->createBlock('\ExtensionsStore\Addressvalidator\Block\Ajax\Popup')
				->setTemplate('ExtensionsStore_Addressvalidator::ajax/popup.phtml');
				
				if (is_array($resultObjs)){
					$popup->setResults($resultObjs);
					foreach ($resultObjs as $serviceCode=>$results){
						foreach ($results as $result){
							if (!$result->getError()){
								$resultData = $result->getData();
								$resultData['service'] = $serviceCode;
								$resultData['label'] = $result->getAddressString();
								$resultsAr[] = $resultData;
							}
						}
					}
				} else if (is_string($resultObjs)){
					$popup->setErrorMessage($resultObjs);
				}
				
				$html = $popup->toHtml();
				$errorFields = $validator->getErrorFields();
				$resultData['error'] = false;
				$resultData['data'] = ['html'=>$html, 'results'=> $resultsAr, 'error_fields' => $errorFields];
				
				$resultJson->setData($resultData);
				
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
