<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Adminhtml_Sales_Order_Analysis_ResultController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Postdirekt_Addressfactory_Model_Order_Analysis
     */
    private $orderAnalysis;

    /**
     * @var Postdirekt_Addressfactory_Model_Order_Updater
     */
    private $orderUpdater;

    public function _construct()
    {
        $this->orderAnalysis = Mage::getSingleton('postdirekt_addressfactory/order_analysis');
        $this->orderUpdater = Mage::getSingleton('postdirekt_addressfactory/order_updater');
    }

    /**
     * Save suggested improvement from analysis result to order address.
     *
     * @return void
     */
    public function applyAction()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        /** @var Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult */
        $analysisResult = Mage::getModel('postdirekt_addressfactory/analysis_result')->load($order->getShippingAddressId());

        if (!$analysisResult->getId()) {
            $this->_getSession()->addError($this->__('Could not apply ADDRESSFACTORY DIRECT address suggestion for order.'));
        } else {
            $wasUpdated = $this->orderUpdater->updateShippingAddress($order, $analysisResult);
            if ($wasUpdated) {
                $this->_getSession()->addSuccess($this->__('Order address updated with ADDRESSFACTORY DIRECT suggestion.'));
            } else {
                $this->_getSession()->addError($this->__('Could not update order address with ADDRESSFACTORY DIRECT suggestion.'));
            }
        }

        $this->_redirectReferer();
    }

    /**
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/order/actions/edit');
    }
}
