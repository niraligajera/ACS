<?php 
namespace NG\Acs\Ui\DataProvider\Product; 

class CustomColumnFilter implements \Magento\Ui\DataProvider\AddFilterToCollectionInterface 
{ 
    public function addFilter(
        \Magento\Framework\Data\Collection $collection,
        $field,
        $condition = null
    ) 
    { 
        if (isset($condition['like'])) 
        { 
            $collection->addFieldToFilter($field, $condition); 
        } 
    } 
}