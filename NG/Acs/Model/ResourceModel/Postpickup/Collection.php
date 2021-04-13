<?php
namespace  NG\Acs\Model\ResourceModel\Postpickup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'acs_shipping_pickup_module_collection';
	protected $_eventObject = 'Postpickup_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('NG\Acs\Model\Postpickup', 'NG\Acs\Model\ResourceModel\Postpickup');
	}

}