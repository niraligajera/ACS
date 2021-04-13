<?php
/**
 * Hello Employee Index Controller. 
 * @package   NG_Acs
 * @author    NG
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com) 
 */
namespace  NG\Acs\Controller\Adminhtml\AcsStaff;

use Magento\Framework\App\ResponseInterface;


class Pick extends \Magento\Backend\App\Action
{
   /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $_resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $_logger;

    /**
     * AbstractAction constructor.
     *
     * @param Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_eventManager = $context->getEventManager();
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;

    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('NG_Acs::voucher_list');
        $resultPage->getConfig()->getTitle()->prepend(__('Print Pickp ACS Collection'));
        $resultPage->addBreadcrumb(__('Pickp Acs'), __('Pickp ACS'));
        $resultPage->addBreadcrumb(__('Manage  Pickp Collection'), __('Manage Pickp Collection'));

        return $resultPage;
    }

    /**
     * Check Order Import Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('NG_Acs::voucher_pickp');
    }
}
