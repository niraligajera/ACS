<?php
namespace NG\Acs\Observer;

class SetOrderAttribute implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();       
        $order->setVoucher("555555");       
        return $this;
    }
}
