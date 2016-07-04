<?php

namespace ExtensionsStore\Addressvalidator\Model;

class Observer {

	public function validateAddress(\Magento\Framework\Event\Observer $observer) {
		
		
		$data = $observer->getData();
		
	}
}
