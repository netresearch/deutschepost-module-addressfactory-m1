<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _toHtml()
    {
        return '';
    }

    /**
     * Filter grid by deliverability status
     *
     * @param Mage_Sales_Model_Resource_Order_Grid_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return $this
     */
    public function filterStatus(
        Mage_Sales_Model_Resource_Order_Grid_Collection $collection,
        Mage_Adminhtml_Block_Widget_Grid_Column $column
    ) {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection->join(
            ['status_table' => 'postdirekt_addressfactory/analysis_status'],
            'main_table.entity_id = status_table.order_id',
            ['deliverability_status' => 'status']
        );
        $collection->addFieldToFilter('status_table.status', ['eq' => $value]);

        return $this;
    }
}
