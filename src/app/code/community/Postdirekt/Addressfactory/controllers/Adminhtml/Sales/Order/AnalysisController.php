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
     * Returns orders with shipping address in Germany.
     *
     * @param string[] $orderIds
     * @return Mage_Sales_Model_Order[]
     */
    private function getOrders(array $orderIds): array
    {
        try {
            $orderCollection = Mage::getModel('sales/order')->getCollection();
        } catch (Mage_Core_Exception $e) {
            return [];
        }

        $orderCollection->getSelect()->join(
            ['order_address' => $orderCollection->getTable('sales/order_address')],
            'main_table.entity_id = order_address.parent_id',
            ['address_type', 'country_id']
        );
        $orderCollection->addAttributeToFilter('order_address.address_type', 'shipping');
        $orderCollection->addAttributeToFilter('order_address.country_id', 'DE');
        $orderCollection->addFieldToFilter('parent_id', ['in' => $orderIds]);

        return $orderCollection->getItems();
    }

    /**
     * Send orders to analysis service and store results. Update order status if applicable (hold, cancel) and apply
     * address suggestions.
     */
    public function massAnalyzeAction()
    {
        $heldOrderIds = [];
        $updatedOrderIds = [];
        $canceledOrderIds = [];
        $failedOrderIds = [];

        $orders = $this->getOrders($this->getRequest()->getParam('order_ids'));
        $analysisResults = $this->orderAnalysis->analyse($orders);

        foreach ($orders as $order) {
            $analysisResult = $analysisResults[(int) $order->getId()];
            if (!$analysisResult) {
                $failedOrderIds[] = $order->getIncrementId();
                continue;
            }

            if ($this->config->isAutoCancelOrders()) {
                $isCanceled = $this->orderUpdater->cancelIfUndeliverable($order, $analysisResult);
                if ($isCanceled) {
                    $canceledOrderIds[] = $order->getIncrementId();
                }
            }

            if ($this->config->isAutoUpdateShippingAddress()) {
                $isUpdated = $this->orderUpdater->updateShippingAddress($order, $analysisResult);
                if ($isUpdated) {
                    $updatedOrderIds[] = $order->getIncrementId();
                }
            }

            if ($this->config->isHoldNonDeliverableOrders()) {
                $isOnHold = $this->orderUpdater->holdIfNonDeliverable($order, $analysisResult);
                if ($isOnHold) {
                    $heldOrderIds[] = $order->getIncrementId();
                }
            }
        }

        if (!empty($heldOrderIds)) {
            $this->_getSession()->addSuccess(
                $this->__('Non-deliverable order(s) %s put on hold.', implode(', ', $heldOrderIds))
            );
        }
        if (!empty($updatedOrderIds)) {
            $this->_getSession()->addSuccess(
                $this->__('Order(s) %s were successfully updated.', implode(', ', $updatedOrderIds))
            );
        }
        if (!empty($canceledOrderIds)) {
            $this->_getSession()->addSuccess(
                $this->__('Undeliverable order(s) %s canceled.', implode(', ', $canceledOrderIds))
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
     * Single order analysis
     */
    public function analyzeAction()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        try {
            $analysisResults = $this->orderAnalysis->analyse([$order]);
            $analysisResult = $analysisResults[$orderId];
            if (!$analysisResult) {
                throw new RuntimeException($this->__('Could not perform ADDRESSFACTORY DIRECT analysis for order.'));
            }

            if ($this->config->isAutoCancelOrders()) {
                $isCanceled = $this->orderUpdater->cancelIfUndeliverable($order, $analysisResult);
                if ($isCanceled) {
                    $this->_getSession()->addSuccess($this->__('Undeliverable order canceled.', $order->getIncrementId()));
                }
            }

            if ($this->config->isAutoUpdateShippingAddress()) {
                $isUpdated = $this->orderUpdater->updateShippingAddress($order, $analysisResult);
                if ($isUpdated) {
                    $this->_getSession()->addSuccess($this->__('Order address updated with ADDRESSFACTORY DIRECT suggestion.'));
                }
            }

            if ($this->config->isHoldNonDeliverableOrders()) {
                $isOnHold = $this->orderUpdater->holdIfNonDeliverable($order, $analysisResult);
                if ($isOnHold) {
                    $this->_getSession()->addSuccess($this->__('Non-deliverable order put on hold.'));
                }
            }
        } catch (Throwable $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }

    protected function _isAllowed(): bool
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/order/actions/edit');
    }
}
