<?php
/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Adminhtml_Sales_Order_AnalysisController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Postdirekt_Addressfactory_Model_Order_Analysis
     */
    protected $orderAnalysis;

    /**
     * @var Postdirekt_Addressfactory_Model_Config
     */
    protected $config;

    /**
     * @var Postdirekt_Addressfactory_Model_Order_Updater
     */
    protected $orderUpdater;

    public function _construct()
    {
        $this->orderAnalysis = Mage::getSingleton('postdirekt_addressfactory/order_analysis');
        $this->orderUpdater = Mage::getSingleton('postdirekt_addressfactory/order_updater');
        $this->config = Mage::getSingleton('postdirekt_addressfactory/config');
    }

    /**
     * MassAction for Order analysis
     */
    public function massAnalyze()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->join(
            'sales_order_address',
            'main_table.entity_id=sales_order_address.parent_id',
            ['address_type', 'country_id']
        );
        $orderCollection->addAttributeToFilter('sales_order_address.address_type', 'shipping');
        $orderCollection->addAttributeToFilter('sales_order_address.country_id', 'DE');
        $orderCollection->addFieldToFilter('order_id', ['in' => $orderIds]);

        try {
            $analysisResults = $this->orderAnalysis->analyse($orderCollection->getItems());
        } catch (Throwable $exception) {
            $this->_getSession()->addError($exception->getMessage());
            $this->_redirectReferer();
            return;
        }

        $heldOrderIds = [];
        $canceledOrderIds = [];
        $failedOrderIds = [];
        /** @var Mage_Sales_Model_Order $order */
        foreach ($orderCollection->getItems() as $order) {
            $analysisResult = $analysisResults[(int)$order->getEntityId()];
            if (!$analysisResult) {
                $failedOrderIds[] = $order->getIncrementId();
                continue;
            }
            if ($this->config->isHoldNonDeliverableOrders()) {
                $isOnHold = $this->orderUpdater->holdIfNonDeliverable($order, $analysisResult);
                if ($isOnHold) {
                    $heldOrderIds[] = $order->getIncrementId();
                }
            }
            if ($this->config->isAutoCancelOrders()) {
                $isCanceled = $this->orderUpdater->cancelIfUndeliverable($order, $analysisResult);
                if ($isCanceled) {
                    $canceledOrderIds[] = $order->getIncrementId();
                }
            }
        }
        if (!empty($heldOrderIds)) {
            $this->_getSession()->addSuccess(
                $this->__('Non-deliverable Order(s) %s were put on hold.', implode(', ', $heldOrderIds))
            );
        }
        if (!empty($canceledOrderIds)) {
            $this->_getSession()->addSuccess(
                $this->__('Undeliverable Order(s) %s were canceled.', implode(', ', $canceledOrderIds))
            );
        }
        if (!empty($failedOrderIds)) {
            $this->_getSession()->addError(
                $this->__('Order(s) %s could not be analysed with ADDRESSFACTORY DIRECT.', implode(', ', $failedOrderIds))
            );
        }

        $this->_redirectReferer();
    }

    /**
     * Single Order analysis
     */
    public function analyze()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        try {
            $analysisResults = $this->orderAnalysis->analyse([$order]);
            $analysisResult = $analysisResults[$orderId];
            if (!$analysisResult) {
                throw new RuntimeException($this->__('Could not perform ADDRESSFACTORY DIRECT analysis for Order'));
            }
        } catch (Throwable $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        if ($this->config->isHoldNonDeliverableOrders($order->getStoreId())) {
            $isOnHold = $this->orderUpdater->holdIfNonDeliverable($order, $analysisResult);
            if ($isOnHold) {
                $this->_getSession()->addSuccess($this->__('Non-deliverable Order put on hold'));
            }
        }
        if ($this->config->isAutoCancelOrders($order->getStoreId())) {
            $isCanceled = $this->orderUpdater->cancelIfUndeliverable($order, $analysisResult);
            if ($isCanceled) {
                $this->_getSession()->addSuccess($this->__('Undeliverable Order canceled', $order->getIncrementId()));
            }
        }
        if ($this->config->isAutoUpdateShippingAddress($order->getStoreId())) {
            $isUpdated = $this->orderUpdater->updateShippingAddress($order, $analysisResult);
            if ($isUpdated) {
                $this->_getSession()->addSuccess($this->__('Order address updated with ADDRESSFACTORY DIRECT suggestion'));
            }
        }

        $this->_redirectReferer();
    }

    protected function _isAllowed(): bool
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/order/actions/edit');
    }
}
