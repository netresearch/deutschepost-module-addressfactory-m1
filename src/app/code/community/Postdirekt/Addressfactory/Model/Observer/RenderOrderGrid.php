<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Observer_RenderOrderGrid
{
    /**
     * @var Postdirekt_Addressfactory_Model_Order_Status
     */
    private $orderStatus;

    public function __construct()
    {
        $this->orderStatus = Mage::getSingleton('postdirekt_addressfactory/order_status');
    }

    /**
     * Add "Create Shipping Labels" mass action to order grid.
     * - event: adminhtml_block_html_before
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function addAnalysisMassAction(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if (!$block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction) {
            // not a mass action block at all
            return;
        }

        if ($block->getRequest()->getControllerName() !== 'sales_order') {
            // not an order grid mass action block
            return;
        }

        $itemsData = [
            'postdirekt_addressfactory_check' => [
                'label' => Mage::helper('postdirekt_addressfactory/data')->__('[ADDRESSFACTORY] Check Shipping Address'),
                'url' => $block->getUrl('adminhtml/sales_order_analysis/massAnalyze'),
            ],
        ];

        foreach ($itemsData as $itemId => $itemData) {
            $block->addItem($itemId, $itemData);
        }
    }

    /**
     * Join analysis status table to grid table.
     *
     * - event: sales_order_grid_collection_load_before
     *
     * @param Varien_Event_Observer $observer
     * @throws Zend_Db_Select_Exception
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
     *
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
