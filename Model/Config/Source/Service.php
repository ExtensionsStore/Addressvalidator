<?php
/**
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Model\Config\Source;

class Service implements \Magento\Framework\Option\ArrayInterface
{
	protected $_serviceModel;
	
	public function __construct(\ExtensionsStore\Addressvalidator\Model\Service $serviceModel){
		
		$this->_serviceModel = $serviceModel;
	}
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$services = $this->_serviceModel->getServices();
        return $services;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
    	$servicesAr = [];
    	$services = $this->_serviceModel->getServices();
    	foreach ($services as $key=> $service){
    		$servicesAr[$key] = $service['label'];
    	}
    	
    	return $servicesAr;
    }
}
