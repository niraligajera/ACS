<?php

namespace NG\Acs\Controller\Adminhtml\Postpickup;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use NG\Acs\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use \NG\Acs\Helper\Data;
/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    protected $formKey;


    public function __construct(
        Context $context,
        Filter $filter,
        Data $helperData, 
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\App\Request\Http $request,
        CollectionFactory $collectionFactory        
    ) {
        
        parent::__construct($context, $filter);
        $this->helperData = $helperData;
        $this->request = $request;
        $this->formKey = $formKey;
        $this->request->setParam('form_key', $this->formKey->getFormKey());
        $this->collectionFactory = $collectionFactory;        
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
        $notid = '';
        foreach ($collection as $order) {    
                  
            $retunJson = json_decode($order['form_json']);
            $retunJson =  $retunJson->ACSOutputResponce;            
            $ids[] = $retunJson->ACSValueOutput[0]->Voucher_No;                   
           
        }

        $model = $this->_objectManager->create('NG\Acs\Model\Post');           
        if(count($ids) > 0){
            $countDeleteOrder = 0;
            
            $post['Company_Password'] = $this->helperData->getGeneralConfig('company_pass');
            $post['Company_ID'] =  $this->helperData->getGeneralConfig('company_id');
            $post['User_ID'] = $this->helperData->getGeneralConfig('user_id');
            $post["User_Password"] =   $this->helperData->getGeneralConfig('user_pass');
            $post["Language"] = 'GR';
            $post["Voucher_No"] = implode(', ', $ids);                                
            $data1['ACSAlias'] = "ACS_Delete_Voucher";
            $data1['ACSInputParameters'] = $post;                                               
            $data = json_encode($data1);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://webservices.acscourier.net/ACSRestServices/api/ACSAutoRest');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,  $data);

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Accept: application/json';
            $headers[] = 'Acsapikey: '.$this->helperData->getGeneralConfig('api_key');

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $from = date("Y-m-d 00:00:00");
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);    
            $finalData = json_decode($result);             
            if(@$finalData->ACSOutputResponce->ACSValueOutput[0]->Error_Message == '' && @$finalData->ACSOutputResponce->ACSTableOutput->Table_Data[0]->voucher_no != ''){
                foreach ($collection as $order) { 
                    if (!$order->getId()) {
                        continue;
                    }
                    $loadedOrder = $model->load($order->getId());
                    $loadedOrder->delete();                  
                    $countDeleteOrder++;
                }
                $this->messageManager->addSuccess(__('You have deleted %1 order(s). is '. $post["Voucher_No"]));
            }else{
                foreach ($collection as $order) { 
                    if (!$order->getId()) {
                        continue;
                    }
                    $loadedOrder = $model->load($order->getId());
                    $loadedOrder->delete();                  
                    $countDeleteOrder++;
                }
                $this->messageManager->addError(__($finalData->ACSOutputResponce->ACSTableOutput->Table_Data[0]->error_message));
                // return $resultRedirect;
            }
           
        }else{
                foreach ($collection as $order) { 
                    if (!$order->getId()) {
                        continue;
                    }
                    $loadedOrder = $model->load($order->getId());
                    $loadedOrder->delete();                  
                  
                }
            $this->messageManager->addError(__($notid.'  Already print so you can not allow to delete..'));
           
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}