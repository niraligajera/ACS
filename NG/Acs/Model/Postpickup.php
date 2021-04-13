<?php
namespace  NG\Acs\Model;

class Postpickup extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'acs_shipping_pickup_module';

	protected $_cacheTag = 'acs_shipping_pickup_module';

	protected $_eventPrefix = 'acs_shipping_pickup_module';

	protected function _construct()
	{
		$this->_init('NG\Acs\Model\ResourceModel\Postpickup');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}