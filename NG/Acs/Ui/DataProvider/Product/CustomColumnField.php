<?php 
namespace NG\Acs\Ui\DataProvider\Product;

class CustomColumnField implements \Magento\Ui\DataProvider\AddFieldToCollectionInterface 
{ 
    public function addField(
        \Magento\Framework\Data\Collection $collection,
        $field,
        $alias = null
    ) 
    { 
        $collection->joinField(
             'custom_column', 
             'table_name', 
             'custom_column', 
             'customfield_id=entity_id',
             null, 
             'left' 
        );
    }
}