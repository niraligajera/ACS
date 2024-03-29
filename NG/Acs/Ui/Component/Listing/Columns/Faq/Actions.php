<?php
/**
 * Mage-World
 *
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace NG\Acs\Ui\Component\Listing\Columns\Faq;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class ProductActions
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                // $order2 = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementID((int)$item['order_id']; 
                // $shipmentCollection = $order2->getShipmentsCollection();
                // foreach ($_order->getShipmentsCollection() as $_shipment) {
                //     $tracks = $_shipment->getTracksCollection();
                //     if ($tracks->count()) {
                         
                          $item[$this->getData('name')]['edit'] =  [
                                        'href' => $this->urlBuilder->getUrl(
                                            'acs/acsstaff/print',
                                            ['id' => $item['order_id']]
                                        ),
                                        'label' => __('View'),
                                        'hidden' => false,
                                    ];
                //     }
            }
               
                           
        }

        return $dataSource;
    }
}
