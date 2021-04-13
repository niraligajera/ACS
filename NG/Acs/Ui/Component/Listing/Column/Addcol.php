<?php

namespace NG\Acs\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;

class Addcol extends \Magento\Ui\Component\Listing\Columns\Column
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
                $retunJson = json_decode($item['pass_parameter']);
                $retunJson =  $retunJson->ACSOutputResponce;
                $item['permission'] = $retunJson->ACSInputParameters->Recipient_Name;            

            }
        }
        return $dataSource;
    }


    function is_valid_json( $raw_json ){
        return ( json_decode( $raw_json , true ) == NULL ) ? false : true ;
    }
}