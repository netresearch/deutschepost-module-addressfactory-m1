<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Observer_RenderOrderView
{
    /**
     * Append analysis data box to the "order_info" block.
     * - event: core_block_abstract_to_html_after
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function addAnalysisData(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if (!$block instanceof Mage_Adminhtml_Block_Sales_Order_View_Info) {
            // not the order info block
            return;
        }

        if ($block->getRequest()->getControllerName() !== 'sales_order') {
            // not the order view page
            return;
        }

        $analysisDataBlock = $block->getChild('analysis_data');
        if (!$analysisDataBlock instanceof Postdirekt_Addressfactory_Block_Adminhtml_Sales_Order_Info_Analysis) {
            // block not found
            return;
        }

        $transport = $observer->getData('transport');
        $html      = $transport->getHtml() . $analysisDataBlock->toHtml();
        $transport->setHtml($html);
    }
}
