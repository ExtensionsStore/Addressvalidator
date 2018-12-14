<?php
/**
 * JS config
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */

namespace ExtensionsStore\Addressvalidator\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class AddressvalidatorConfigProvider implements ConfigProviderInterface {
	protected $_scopeConfig;
	public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
		$this->_scopeConfig = $scopeConfig;
	}
	
	/**
	 *
	 * {@inheritdoc}
	 */
	public function getConfig() {
		$config['addressvalidator'] =  $this->_scopeConfig->getValue('extensions_store_addressvalidator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				
		return $config;
	}
}

