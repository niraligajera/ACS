<?php
namespace  NG\Acs\Model\ResourceModel;


class Postpickup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('acs_shipping_pickup_module', 'id');
	}

}