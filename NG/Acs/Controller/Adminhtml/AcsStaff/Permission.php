<?php

namespace NG\Acs\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;

class Permission extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
         \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function prepareDataSource(array $dataSource) {

        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $html = '';
                $per_actual = '';
                $customerID = 10; // your customer-id
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customerObj = $objectManager->create('Magento\Customer\Model\Customer')
                            ->load($item['order_id']);
                $customerFirstName = $customerObj->getFirstname();
                if($item['status'] == 'created_vocher')
                {
                     $item['status'] = 'CREATED (Not Printed Yet)';
                     
                }else if($item['status'] == 'Printed')
                {
                    $item['status'] = ' ALREADY PRINTED';
                }
                else if($item['status'] == 'Pickup_up')
                {
                    $item['status'] = 'Shipped Out';
                }
                 else if($item['status'] == 'Pickup_up_not_print')
                {
                    $item['status'] = 'CREATED (Not Printed Yet)';
                }                
                $item['status'] = '<span style="color:red;" >'.$item['status'].'</span>';

            }
        }
        // echo '<pre>'; print_r($dataSource['data']); die;

        return $dataSource;
    }


    function is_valid_json( $raw_json ){
        return ( json_decode( $raw_json , true ) == NULL ) ? false : true ;
    }
}