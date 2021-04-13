<?php

namespace NG\Acs\Controller\Adminhtml\AcsStaff;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use NG\Acs\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use \NG\Acs\Helper\Data;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
/**
 * Class MassDelete
 */
class MassPrint extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ShipmentTrackInterfaceFactory
     */
    private $trackFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Action\Context $context
     * @param ShipmentLoader $shipmentLoader
     * @param ShipmentRepositoryInterface|null $shipmentRepository
     * @param ShipmentTrackInterfaceFactory|null $trackFactory
     * @param SerializerInterface|null $serializer
     */
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Data $helperData, 
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentTrackInterfaceFactory $trackFactory,
        ShipmentLoader $shipmentLoader,
        SerializerInterface $serializer = null
    ) {
        parent::__construct($context, $filter);
        $this->helperData = $helperData;
        $this->request = $request;
        $this->_downloader =  $fileFactory;
        $this->formKey = $formKey;
        $this->request->setParam('form_key', $this->formKey->getFormKey());
        $this->collectionFactory = $collectionFactory;         
        $this->shipmentLoader = $shipmentLoader;
        $this->shipmentRepository = $shipmentRepository ?: ObjectManager::getInstance()
            ->get(ShipmentRepositoryInterface::class);
        $this->trackFactory = $trackFactory ?: ObjectManager::getInstance()
            ->get(ShipmentTrackInterfaceFactory::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(SerializerInterface::class);
    
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
 
        $collection = $this->filter->getCollection($this->collectionFactory->create());        
        $collectionSize = $collection->getSize();
        $ids = [];
        $notId = [];
        $carrier = 'custom';      
        $title ='ACS voucher shipment';
        $shipment = '';
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/acsshipping.log');   
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $model = $this->_objectManager->create('NG\Acs\Model\Post');
        foreach ($collection as $order) { 
            if($order['status'] != 'Pickup_up'){
                $model->load($order->getId())->setStatus('Printed')->save();
                $retunJson = json_decode($order['form_json']);
                $retunJson =  $retunJson->ACSOutputResponce;
                $ids[] = $retunJson->ACSValueOutput[0]->Voucher_No;
                $number = $retunJson->ACSValueOutput[0]->Voucher_No;                           
                $logger->info('------'.$order->getId().'------Printed--Vopucher Number'.@$number);             

            }else{
                $notId[] =  $order['voucher_number'];
                $logger->info('------'.$order->getId().'------ not Printed--Vopucher Number'.@$order['voucher_number']);             

            }
                
        }    

        if(count($ids) > 0){
           $countDeleteOrder = 0;
            $model = $this->_objectManager->create('NG\Acs\Model\Post');           
            $post['Company_Password'] = $this->helperData->getGeneralConfig('company_pass');
            $post['Company_ID'] =  $this->helperData->getGeneralConfig('company_id');
            $post['User_ID'] = $this->helperData->getGeneralConfig('user_id');
            $post["User_Password"] =   $this->helperData->getGeneralConfig('user_pass');
            $post["Voucher_No"] = implode('|', $ids);                       
            $url = 'https://acs-eud2.acscourier.net/Eshops/GetVoucher.aspx?MainID='.$post['Company_ID'].'&MainPass='.$post['Company_Password'].'&UserID='.$post['User_ID'].'&UserPass='.$post["User_Password"].'&voucherno='. $post["Voucher_No"].'&PrintType='.$this->getRequest()->getParam('print');
            if( $this->getRequest()->getParam('StartFromNumber') != '')
            {
                $url .= '&StartFromNumber='.$this->getRequest()->getParam('StartFromNumber');
            }     
            $logger->info('------------Printed-->'.$url);      
            $pdfContent = file_get_contents($url); 
            $name='myfile_'.date('m-d-Y_hia').'.txt';            
            $this->messageManager->addSuccess(__('You have print %1 order(s).'));   
                $this->_downloader->create(
                        'myfile'.rand().'.pdf',
                        @file_get_contents($url),
                        \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                        'application/pdf'
                );
        }elseif ($notId){
            $d = implode(',', $notId);
            $this->messageManager->addError(__($d.' not have today Pickup_Date or Shpiing already Printed')); 
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
   
    }
  

}