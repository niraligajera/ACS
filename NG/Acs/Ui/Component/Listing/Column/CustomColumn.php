<?php

namespace NG\Acs\Ui\Component\Listing\Column;


use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class CustomColumn extends Column
{
    protected $_searchCriteria;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $criteria,
        array $components = [],
        array $data = [])
    {
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                $arr = []; //add data      
                
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface')
                            ->get($items["entity_id"]);                
                $items['voucher'] = $order->getData("order_notes");

               
            }
        }
        return $dataSource;
    }
}