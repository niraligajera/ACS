<?php
namespace NG\Acs\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\ResultFactory;
use \NG\Acs\Helper\Data;
use \Magento\Sales\Model\Order;


class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $session;

    public function __construct(
        Context $context,
        Session $customerSession,
        Data $helperData,        
        Order $Order,        
        PageFactory $resultPageFactory  
    ){
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;                
        $this->order = $Order;
        return parent::__construct($context);
    }

    public function execute()
    { 

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/acsshipping.log');   
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);  
        try {                 
                $post = $this->getRequest()->getPostValue();  
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();                  
                $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($post['order_id']);                  
                $model = $objectManager->create('NG\Acs\Model\Post');     
                    $post = $this->getRequest()->getPostValue();                                       
                    $amount =  $post['Cod_Ammount'];
                    if($this->getRequest()->getPostValue()){ 

                        $orderdetails = $this->order->load($post['order_id']);      
                        $post['Billing_Code'] = $this->helperData->getGeneralConfig('bill_code');                    
                        if(@$post['Acs_Delivery_Products_set']){
                                $post['Acs_Delivery_Products'] = implode(',',$post['Acs_Delivery_Products_set']);                        
                        }
                        $post["Acs_Delivery_Products"] =  @$post['Acs_Delivery_Products'];
                        $post["Insurance_Ammount"] = null;
                        // $post["Delivery_Notes"] = null;
                        $post["Appointment_Until_Time"] = null;
                        $post["Recipient_Email"] = null;
                        // $post["Reference_Key1"] = $post['relevant1'] ? $post['relevant1'] :'';
                        // $post["Reference_Key2"] = $post['relevant2'] ? $post['relevant2'] :'';
                        $post["With_Return_Voucher"] = null;
                        $post["Language"]="GR";     
                        $post['Cod_Ammount'] = $amount > 0 ? (float)$amount : null;                        
                        $post['Weight'] =  (float)$post['Weight'];                        
                        $post["Acs_Station_Destination"] = null;                        
                        $post['Recipient_Region']=  $post['area'];
                        $post["Acs_Station_Branch_Destination"] = '1';                        
                        $post["Sender_Address"] =  null;
                        $post["Sender_Zipcode"] = null;
                        $post["Sender_Region"]= null;
                        $post["Sender_Phone"]= null;
                        $post["Dimension_X_In_Cm"] = $post["Dimension_X_In_Cm"] ? $post["Dimension_X_In_Cm"] : null;
                        $post["Dimension_Y_in_Cm"] = $post["Dimension_Y_in_Cm"] ? $post["Dimension_Y_in_Cm"] : null;
                        $post["Dimension_Z_in_Cm"] = $post["Dimension_Z_in_Cm"] ? $post["Dimension_Z_in_Cm"] : null;
                                      
                        $data1['ACSAlias'] = "ACS_Create_Voucher";                       

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
                        $headers[] = 'Acsapikey: '.$post['api_key'];

                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        $from = date("Y-m-d 00:00:00");
                        $result = curl_exec($ch);
                        if (curl_errno($ch)) {
                            echo 'Error:' . curl_error($ch);
                        }
                        curl_close($ch);     
                        $finalData = json_decode($result);                    
                        // echo '<pre>'; print_r($finalData); die();
                        $logger->info('---------------------------------------------/n');
                        $logger->info('ACS_Create_Voucher-----------'. $data); 
                        $logger->info('ACS_Create_Voucher-----------'. $result); 
                        $logger->info('---------------------------------------------/n');

                        $Modeldata = array(
                            'form_json' =>  $result,
                            'acs_type' => 'ACS_Create_Voucher',
                            'order_id' => $post['order_id'],                            
                            'customer_id' =>$orderdetails->getCustomerEmail(),
                            'pass_parameter' => $data,
                            'created_at' => $from ,
                            'title' => 'ACS_Create_Voucher',  
                            'customer_name' =>  $orderdetails->getCustomerName(),
                            'Pickup_Date' => $post['Pickup_Date'],
                            'Sender' => $post['Sender'],
                            'phone_number' => $post['Recipient_Phone'],
                            'tname' => $post['Recipient_Name'],
                            'Cod_Ammount' => $post['Cod_Ammount'],
                            'status'=>'created_vocher',
                            'shipping_id' => $post['ship_id']
        
                        );

                        if(@$finalData->ACSOutputResponce->ACSValueOutput[0]->Voucher_No != '' && $finalData->ACSOutputResponce->ACSValueOutput[0]->Voucher_No > 0 ){
                            $voucher = @$finalData->ACSOutputResponce->ACSValueOutput[0]->Voucher_No;       
                            $Modeldata['permission'] =$post['Cod_Ammount'] > 0 ? $post['Recipient_Name'] : $orderdetails->getCustomerName();
                            $Modeldata['voucher_number'] = $voucher;
                            $model->setData($Modeldata);
                            $order->setOrderNotes($voucher);
                            // $order->save();
                            $model->save();
                            $responseContent = [
                                'success' => true,                            
                                'code' => $result,
                                'message' =>'voucher created with .'.$voucher
                            ];
                        }else{
                            $responseContent = [
                                'success' => false,                            
                                'code' => $finalData,
                                'message' => @$finalData->ACSOutputResponce->ACSValueOutput[0]->Error_Message
                            ];
                        }   

                    }else{
                        $responseContent = [
                            'error' => true,
                            'error_message' => 'Parameter is not pass'             
                        ];
                    }
                // }
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage(),              
                'code' => '',
            ];
        } catch (\Exception $e) {
            
            $responseContent = [
                'success' => false,
                'error_message' => __('We can\'t create vouher right now.'.$e->getMessage()),             
                'code' => '',
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
        
    }
}