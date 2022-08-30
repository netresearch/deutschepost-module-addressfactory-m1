<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Observer_DeliverabilityStatus
{
    /**
     * @var Postdirekt_Addressfactory_Model_Config
     */
    private $config;

    /**
     * @var Postdirekt_Addressfactory_Model_Order_Status
     */
    private $orderStatus;

    public function __construct()
    {
        $this->config = Mage::getSingleton('postdirekt_addressfactory/config');
        $this->orderStatus = Mage::getSingleton('postdirekt_addressfactory/order_status');
    }

    public function initDeliverabilityStatus(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->getShippingAddress() === false) {
            // order is virtual or broken
            return;
        }
        if ($order->getShippingAddress()->getCountryId() !== "DE") {
            // Addressfactory is only available for german addresses
            return;
        }
        $storeId = (string) $order->getStoreId();
        if ($this->config->isManualAnalysisOnly($storeId)) {
            // Manual analysis is not handled
            return;
        }


        //@TODO set to not analyzed, handle automatic order checking
    }

    /**
     * join status table to grid table
     * - event: sales_order_grid_collection_load_before
     * @param Varien_Event_Observer $observer
     */
    public function addStatusToOrderGridCollection(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Resource_Order_Grid_Collection $collection */
        $collection = $observer->getData('order_grid_collection');
        if (!array_key_exists('status_table', $collection->getSelect()->getPart('from'))) {
            $collection->getSelect()->joinLeft(
                ['status_table' => $collection->getTable('postdirekt_addressfactory/analysis_status')],
                'main_table.entity_id = status_table.order_id',
                ['deliverability_status' => 'status']
            );
        }
    }

    /**
     * Add new column postdirekt_addressfactory_deliverability_status to sales order grid.
     * - event: core_layout_block_create_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function addColumnToGrid(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid)) {
            return;
        }

        $filterBlock = $block->getLayout()->createBlock('postdirekt_addressfactory/adminhtml_sales_order_grid');

        // Add a new column right after the "Ship to Name" column
        $block->addColumnAfter(
            'deliverability_status',
            [
                'header' => $block->__('Deliverability Status'),
                'index' => 'deliverability_status',
                'renderer' => 'postdirekt_addressfactory/adminhtml_sales_order_grid_renderer_status',
                'type' => 'options',
                'options' => $this->orderStatus->getStatusOptions(),
                'filter_condition_callback' => [$filterBlock, 'filterStatus']
            ],
            'status'
        );
    }
}
