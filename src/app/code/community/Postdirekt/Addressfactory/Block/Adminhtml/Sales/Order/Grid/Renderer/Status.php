<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Block_Adminhtml_Sales_Order_Grid_Renderer_Status extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @var Postdirekt_Addressfactory_Model_Order_Status
     */
    private $orderStatus;

    protected function _construct()
    {
        parent::_construct();

        $this->orderStatus = Mage::getSingleton('postdirekt_addressfactory/order_status');
    }

    /**
     * Convert status code to human readable label.
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row): string
    {
        return $this->orderStatus->getStatusLabel((string) parent::render($row));
    }
}
