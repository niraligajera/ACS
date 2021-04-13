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
class MassStatus extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
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
        \Magento\Framework\Filesystem $filesystem,
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
        $this->filesystem=$filesystem;
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
    \Magento\Framework\App\Action\Context $context,\Magento\Framework\Filesystem $filesystem
    ) {     
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/acsshipping.log');   
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $collection = $this->filter->getCollection($this->collectionFactory->create());        
        $collectionSize = $collection->getSize();
        $ids = [];
        $notId = [];
        $carrier = 'custom';      
        $title ='ACS Courier';
        $shipment = '';
        $numbers = [];
        $model = $this->_objectManager->create('NG\Acs\Model\Post');
        $modelpickup = $this->_objectManager->create('NG\Acs\Model\Postpickup');
        foreach ($collection as $order) {                    
                $retunJson = json_decode($order['form_json']);
                $retunJson =  $retunJson->ACSOutputResponce;
                $ids[] = $retunJson->ACSValueOutput[0]->Voucher_No;
                $number = $retunJson->ACSValueOutput[0]->Voucher_No;          
                $numbers[] = $order->getId(); 
        }       

 
        if(count($ids) > 0){
            $countDeleteOrder = 0;                      
            $post['Company_Password'] = $this->helperData->getGeneralConfig('company_pass');
            $post['Company_ID'] =  $this->helperData->getGeneralConfig('company_id');
            $post['User_ID'] = $this->helperData->getGeneralConfig('user_id');
            $post["User_Password"] =   $this->helperData->getGeneralConfig('user_pass');
            $post["Language"] = 'GR';
            $post["Pickup_Date"] = date('Y-m-d');
            $post["MyData"] =  1;
            $post["Vouchers_To_Include"] =  null;
            $post["Vouchers_To_Exclude"] = null;
           
            $data1['ACSAlias'] = "ACS_Issue_Pickup_List";
            $data1['ACSInputParameters'] = $post; 
            $data = json_encode($data1);           
            $post["Voucher_No"] = implode('|', $ids);  
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://webservices.acscourier.net/ACSRestServices/api/ACSAutoRest');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,  $data);

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Accept: application/json';
            $headers[] = 'Acsapikey: '. $this->helperData->getGeneralConfig('api_key');

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $from = date("Y-m-d 00:00:00");
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);     
                   $logger->info('---------------------------------------------/n');
            $logger->info('ACS_Issue_Pickup_List------'.@$post["Voucher_No"].'------'. $data); 
            $logger->info('ACS_Issue_Pickup_List------'.@$post["Voucher_No"].'------'. $result); 
            $finalData = json_decode($result);            
            if(@$finalData->ACSOutputResponce->ACSValueOutput[0]->PickupList_No){
                    foreach ($numbers as $key => $value) {
                        $data =  $model->load($value);   

                        $order2 = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementID((int)$data->getOrderId());  
                        $order2->setOrderNotes($data->getVoucherNumber());
                        $order2->save();
                        $shipmentCollection = $order2->getShipmentsCollection();
                        foreach($shipmentCollection as $shipment){
                            $shipmentIncrementId = $shipment->getId();
                        }

                        $tracksCollection = $shipment->getTracksCollection();
                        foreach ($tracksCollection->getItems() as $track) {
                            $trackNumber = $track->getTrackNumber();
                            $carrierName = $track->getTitle();
                        }           
                        $shipment = $this->shipmentRepository->get($shipmentIncrementId);

                         $tracksCollection = $shipment->getTracksCollection();
                        foreach ($tracksCollection->getItems() as $track) {
                            // echo '<pre>'; print_r($track->getData()); die();
                            $track23 = $this->trackFactory->create()->load($track->getId());
                            $track23->delete();
                            // echo '<pre>'; print_r($track23->getData());
                        }
                        $number1 = $data->getVoucherNumber();
                        $track = $this->trackFactory->create()->setNumber(
                            $number
                        )->setCarrierCode(
                            $carrier
                        )->setTitle(
                            $title
                        );
                        $shipment->addTrack($track);
                        $this->shipmentRepository->save($shipment);
                        $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')->notify($shipment);
                        $data->setStatus('Pickup_up')->save();        
                        $post['title']  = $data['title'];
                        $post['voucher_number']  = $data['voucher_number'];
                        $post['order_id']  = $data['order_id'];
                        $post['customer_id']  = $data['customer_id'];
                        $post['shipping_id']  = $data['shipping_id'];
                        $post['acs_type']  = $data['acs_type'];
                        $post['created_at']  = $data['created_at'];
                        $post['pass_parameter']  = $data['pass_parameter'];
                        $post['Cod_Ammount']  = $data['Cod_Ammount'];
                        $post['permission']  = @$finalData->ACSOutputResponce->ACSValueOutput[0]->PickupList_No;
                        $post['tname']  = $data['tname'];
                        $post['phone_number']  = $data['phone_number'];
                        $post['Sender']  = $data['Sender'];
                        $post['status']  = $data['status'];
                        $post['Pickup_Date']  = $data['Pickup_Date'];
                        $post['customer_name']  = $data['customer_name'];
                        $modelpickup->setData($post);
                        $modelpickup->save();
                        $model->delete();
                    }      
                    

                    $url = 'https://acs-eud2.acscourier.net/Eshops/getlist.aspx?MainID='.$post['Company_ID'].'&MainPass='.$post['Company_Password'].'&UserID='.$post['User_ID'].'&UserPass='.$post["User_Password"].'&MassNumber='.$finalData->ACSOutputResponce->ACSValueOutput[0]->PickupList_No.'&DateParal='.date('Y-m-d') ;  
                     $logger->info('ACS_Issue_Pickup_List------URL---->'. $url );                  
                     $pdfContent = file_get_contents($url); 

                     $this->_downloader->create(
                        'pickup'.rand().'.pdf',
                        @file_get_contents($url),
                        \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                        'application/pdf'
                    );
                    $finalData = json_decode($result);                  
                    $this->messageManager->addSuccess(__('You have print %1 order(s).'));  
              }else{                             
                $Table_Data = $finalData->ACSOutputResponce->ACSTableOutput->Table_Data;
                $unprinted_vocuher = [];

                foreach ($Table_Data as $key => $value) {
                 $unprinted_vocuher[] = $value->Unprinted_Vouchers;
                }
            

                 
               	$messageManager = @$finalData->ACSOutputResponce->ACSValueOutput[0]->Error_Message.'-- '.implode(' | ', $unprinted_vocuher) ?: 'no voucher found for pickup list may be already pickup';
                $this->messageManager->addError(__(@$messageManager));
              }   
               
        }
        if($notId)
        {
            $d = implode(',', $notId);
            $logger->info('------Ther is no shipping pick today....');    
            $this->messageManager->addError(__($d.'not have today Pickup_Date')); 
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
   
    }

    public function addCustomTrack($shipmentId)
    {
        $number = 12345;
        $carrier = 'custom';
        $title = 'Custom Title';

        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
            $track = $this->trackFactory->create()->setNumber(
                $number
            )->setCarrierCode(
                $carrier
            )->setTitle(
                $title
            );
            $shipment->addTrack($track);
            $this->shipmentRepository->save($shipment);

        } catch (NoSuchEntityException $e) {
            //Shipment does not exist
        }
    }

     /**
     * @param Shipment $shipment
     * @return \Magento\Shipping\Model\ResourceModel\Order\Track\Collection
     */
    protected function _getTracksCollection(Shipment $shipment)
    {
        $tracks = $this->_trackCollectionFactory->create()->setShipmentFilter($shipment->getId());

        if ($shipment->getId()) {
            foreach ($tracks as $track) {
                $track->setShipment($shipment);
            }
        }
        return $tracks;
    }

}