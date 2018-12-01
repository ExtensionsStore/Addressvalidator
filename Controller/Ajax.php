<?php
/**
 * Address Validator Ajax Controller
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_Addressvalidator
 * @author      Extensions Store <support@extensions-store.com>
 */
namespace ExtensionsStore\Addressvalidator\Controller;

abstract class Ajax extends \Magento\Framework\App\Action\Action {
	
	protected $_layoutFactory;
	protected $_resultRawFactory;
	protected $_resultJsonFactory;
	protected $_pageFactory;
	protected $_checkoutSession;
	protected $_helper;
	protected $_registry;
	protected $_logger;
	protected $_validatorFactory;
	protected $_validatorRepositoryFactory;
	
	public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
			\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
			\Magento\Framework\View\LayoutFactory $layoutFactory,
			\Magento\Framework\View\Result\PageFactory $pageFactory,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Framework\Registry $registry,
			\Psr\Log\LoggerInterface $logger,
			\ExtensionsStore\Addressvalidator\Model\ValidatorFactory $validatorFactory,
			\ExtensionsStore\Addressvalidator\Model\ValidatorRepositoryFactory $validatorRespositoryFactory
			)
	{
		$this->_resultRawFactory = $resultRawFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_layoutFactory = $layoutFactory;
		$this->_pageFactory = $pageFactory;
		$this->_checkoutSession = $checkoutSession;
		$this->_registry = $registry;
		$this->_logger = $logger;
		$this->_validatorFactory = $validatorFactory;
		$this->_validatorRepositoryFactory = $validatorRespositoryFactory;
		return parent::__construct ( $context );
	}
	
	/**
	 *
	 * @param string $field
	 * @return array|string
	 */
	public function getPostData($field = null) {
		$request = $this->getRequest ();
		$data = $request->getPost ();
		// json post
		if ((!$data || count($data) == 0) && $postBody = file_get_contents ( 'php://input' )) {
			$data = ( array ) json_decode ( $postBody );
		}
		if ($field){
			return (isset($data[$field])) ? $data[$field] : null;
		}
		return $data;
	}
	
}
