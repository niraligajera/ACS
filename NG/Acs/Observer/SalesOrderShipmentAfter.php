<?php

namespace NG\Acs\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderShipmentAfter implements ObserverInterface
{
    
	/** @var Magento\Sales\Model\Order\ShipmentRepository */
	protected $_shipmentRepository;

	/** @var Magento\Shipping\Model\ShipmentNotifier */
	protected $_shipmentNotifier;

	/** @var Magento\Sales\Model\Order\Shipment\TrackFactory */
	protected $_trackFactory; //missing ;

	public function __construct(
	  \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier, 
	  \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository, 
	  \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory)
	{
	  $this->_shipmentNotifier = $shipmentNotifier;
	  $this->_shipmentRepository = $shipmentRepository;
	  $this->_trackFactory = $trackFactory;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        echo $shipment->getId(); die();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        $carrier = 'custom';      
        $title ='ACS voucher shipment';
        $trackingNumber = 123456879;
        $this->addTrack($shipment,$carrier, $title, $trackingNumber);


    }


    public function addTrack($shipment, $carrierCode, $description, $trackingNumber) 
	{
	    /** Creating Tracking */
	    /** @var Track $track */
	    $track = $this->_trackFactory->create();
	    $track->setCarrierCode($carrierCode);
	    $track->setDescription($description);
	    $track->setTrackNumber($trackingNumber);
	    $shipment->addTrack($track);
	    $this->_shipmentRepository->save($shipment);

	    /* Notify the customer*/
	    $this->_shipmentNotifier->notify($shipment);
	 }



}