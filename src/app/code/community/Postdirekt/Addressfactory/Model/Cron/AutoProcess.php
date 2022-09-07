<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Cron_AutoProcess
{
    /**
     * @var Postdirekt_Addressfactory_Model_Config
     */
    private $config;

    /**
     * @var Postdirekt_Addressfactory_Model_Order_Analysis
     */
    private $orderAnalysis;

    /**
     * @var Postdirekt_Addressfactory_Model_Order_Updater
     */
    private $orderUpdater;

    /**
     * @var Postdirekt_Addressfactory_Helper_Data
     */
    private $translator;

    public function __construct()
    {
        $this->config = Mage::getSingleton('postdirekt_addressfactory/config');
        $this->orderAnalysis = Mage::getSingleton('postdirekt_addressfactory/order_analysis');
        $this->orderUpdater = Mage::getSingleton('postdirekt_addressfactory/order_updater');
        $this->translator = Mage::helper('postdirekt_addressfactory/data');
    }

    /**
     * Collect all orders that apply for automatic analysis.
     *
     * @param bool $includeEdited Indicate if manually edited addresses should be included.
     * @return Mage_Sales_Model_Order[]
     */
    private function loadOrders(bool $includeEdited): array
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        if (!$orderCollection instanceof Mage_Sales_Model_Resource_Order_Collection) {
            throw new RuntimeException('An error occurred while creating order collection.');
        }

        $orderCollection->getSelect()->join(
            ['status_table' => $orderCollection->getTable('postdirekt_addressfactory/analysis_status')],
            'main_table.entity_id = status_table.order_id'
        );

        if ($includeEdited) {
            $orderStatus = [
                Postdirekt_Addressfactory_Model_Order_Status::PENDING,
                Postdirekt_Addressfactory_Model_Order_Status::MANUALLY_EDITED,
            ];
        } else {
            $orderStatus = [
                Postdirekt_Addressfactory_Model_Order_Status::PENDING,
            ];
        }

        $orderCollection->addFieldToFilter(
            'status_table.' . Postdirekt_Addressfactory_Model_Analysis_Status::STATUS,
            ['in' => $orderStatus]
        );

        return $orderCollection->getItems();
    }

    public function execute(Mage_Cron_Model_Schedule $schedule)
    {
        if (!$this->config->isAutomaticAddressAnalysis()) {
            return;
        }

        $heldOrderIds = [];
        $canceledOrderIds = [];
        $failedOrderIds = [];
        $updatedOrderIds = [];

        $orders = $this->loadOrders($this->config->isAutoValidateManualEdited());
        $analysisResults = $this->orderAnalysis->analyse($orders);

        foreach ($orders as $order) {
            $analysisResult = $analysisResults[(int)$order->getEntityId()];
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

            if ($this->config->isHoldNonDeliverableOrders()) {
                $isOnHold = $this->orderUpdater->holdIfNonDeliverable($order, $analysisResult);
                if ($isOnHold) {
                    $heldOrderIds[] = $order->getIncrementId();
                }
            }

            if ($this->config->isAutoUpdateShippingAddress()) {
                $isUpdated = $this->orderUpdater->updateShippingAddress($order, $analysisResult);
                if ($isUpdated) {
                    $updatedOrderIds[] = $order->getIncrementId();
                }
            }
        }

        $cronMessages = [];
        $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_SUCCESS);

        if (!empty($heldOrderIds)) {
            $cronMessages[] = $this->translator->__('Non-deliverable orders %s were put on hold.', implode(', ', $heldOrderIds));
        }

        if (!empty($canceledOrderIds)) {
            $cronMessages[] = $this->translator->__('Undeliverable orders %s were canceled.', implode(', ', $canceledOrderIds));
        }

        if (!empty($updatedOrderIds)) {
            $cronMessages[] = $this->translator->__('Shipping addresses of orders %s were updated.', implode(', ', $updatedOrderIds));
        }

        if (!empty($failedOrderIds)) {
            $cronMessages[] = $this->translator->__('Order(s) %s could not be analysed with ADDRESSFACTORY DIRECT.', implode(', ', $failedOrderIds));
            $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_ERROR);
        }

        if (!empty($cronMessages)) {
            $schedule->setMessages(implode(' ', $cronMessages));
        }
    }
}
