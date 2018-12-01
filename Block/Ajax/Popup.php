<?php

/**
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Block\Ajax;

class Popup extends \Magento\Framework\View\Element\Template {
	protected $_template = 'ajax/popup.phtml';
	protected $_results;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context, 
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			array $data = []) {
		$this->_scopeConfig = $scopeConfig;
		parent::__construct ( $context, $data );
	}
	
	public function setResults($results){
		$this->_results = $results;
		return $this;
	}
	
	public function getResults(){
		return $this->_results;
	}
	
	public function getPopupMessage(){
		
		$path = ($this->_results) ? 'extensions_store_addressvalidator/messaging/matches_available' : 'extensions_store_addressvalidator/messaging/invalid_address';
		$popupMessage = $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		return $popupMessage;
	}
	
	public function getCustomerServiceUrl(){
		$customerServiceUrl = $this->_scopeConfig->getValue('extensions_store_addressvalidator/configuration/too_many_attempts_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		return $this->getUrl ( $customerServiceUrl );
	}
	
	public function getCookieLifetime(){
		$cookieLifetime = $this->_scopeConfig->getValue('web/cookie/cookie_lifetime', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $cookieLifetime;
	}
}