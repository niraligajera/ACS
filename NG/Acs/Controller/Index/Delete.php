<?php
namespace NG\Acs\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\ResultFactory;
use \NG\Acs\Helper\Data;
use \Magento\Sales\Model\Order;
use \NG\Acs\Model\Post;


class Delete extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $session;

	public function __construct(
		Context $context,
        Session $customerSession,
        Data $helperData,        
        Order $Order,      
        Post $post,
        PageFactory $resultPageFactory	
	){
		$this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;		        
        $this->order = $Order;
        $this->Post = $post;
		return parent::__construct($context);
	}

	public function execute()
	{ 
        try {
                        
                $post = $this->getRequest()->getPostValue();
                if($this->getRequest()->getPostValue()){   
                   
                    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/acsshipping.log');   
                    $logger = new \Zend\Log\Logger();   
                    $logger->addWriter($writer);                       
                    $post['Company_Password'] = $this->helperData->getGeneralConfig('company_pass');
                    $post['Company_ID'] =  $this->helperData->getGeneralConfig('company_id');
                    $post['User_ID'] = $this->helperData->getGeneralConfig('user_id');
                    $post["User_Password"] =   $this->helperData->getGeneralConfig('user_pass');
                    $post["Language"] = 'GR';
                    $post["Voucher_No"] = $post['voucherId'];                                
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
                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);
                    }
                    curl_close($ch);    
                    $finalData = json_decode($result);  
                    $logger->info('jsonson------'.@$post["Voucher_No"].'------'. $data); 
                    $logger->info('result------'.@$post["Voucher_No"].'------'. $result);  
                    if(@$finalData->ACSOutputResponce->ACSValueOutput[0]->Error_Message == '' && @$finalData->ACSOutputResponce->ACSTableOutput->Table_Data[0]->voucher_no != ''){
                      
                        $model = $this->Post->load($post['id']);                    
                        $model->delete();
                        $responseContent = [
                            'success' => true,                            
                            'code' => $result,
                             'message'=>'sucess deleted'
                        ];
                    }else{
                        $responseContent = [
                            'success' => false,                            
                            'code' => $result,
                            'message' => $finalData->ACSOutputResponce->ACSValueOutput[0]->Error_Message
                        ];
                    }                               

                }else{
                    $responseContent = [
                        'error' => true,
                        'message' => 'Delete Parameter is not pass'             
                    ];
                }

            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = [
                'success' => false,
                'message' => $e->getMessage(),              
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