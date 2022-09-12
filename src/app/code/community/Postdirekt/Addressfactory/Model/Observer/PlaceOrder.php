<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class Postdirekt_Addressfactory_Model_Observer_PlaceOrder
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
     * @var Postdirekt_Addressfactory_Model_Order_StatusUpdater
     */
    private $statusUpdater;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->config = Mage::getSingleton('postdirekt_addressfactory/config');
        $this->orderAnalysis = Mage::getSingleton('postdirekt_addressfactory/order_analysis');
        $this->orderUpdater = Mage::getSingleton('postdirekt_addressfactory/order_updater');
        $this->statusUpdater = Mage::getSingleton('postdirekt_addressfactory/order_statusUpdater');
        $this->logger = Mage::getModel('postdirekt_addressfactory/logger');
    }

    public function initDeliverabilityStatus(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->getShippingAddress() === false) {
            // order is virtual or broken
            return;
        }

        if ($order->getShippingAddress()->getCountryId() !== 'DE') {
            // Addressfactory is only available for German addresses
            return;
        }

        $orderId = (int) $order->getId();

        if ($this->config->isManualAnalysisOnly()) {
            $this->statusUpdater->setStatusNotAnalyzed($orderId);
            return;
        }

        $status = $this->statusUpdater->getStatus($orderId);

        if ($status !== Postdirekt_Addressfactory_Model_Order_Status::NOT_ANALYSED) {
            // The order already has been analysed
            return;
        }

        if ($this->config->isAutomaticAddressAnalysis()) {
            // Pending status means the cron will pick up the order
            $this->statusUpdater->setStatusPending($orderId);
            return;
        }

        if ($this->config->isAnalysisOnOrderPlace()) {
            $analysisResults = $this->orderAnalysis->analyse([$order]);
            $analysisResult = $analysisResults[$orderId];
            if (!$analysisResult) {
                $this->logger->error(
                    sprintf('ADDRESSFACTORY DIRECT: Order %s could not be analysed', $order->getIncrementId())
                );
                return;
            }

            if ($this->config->isAutoCancelOrders()) {
                $this->orderUpdater->cancelIfUndeliverable($order, $analysisResult);
            }

            if ($this->config->isHoldNonDeliverableOrders()) {
                $this->orderUpdater->holdIfNonDeliverable($order, $analysisResult);
            }

            if ($this->config->isAutoUpdateShippingAddress()) {
                $this->orderUpdater->updateShippingAddress($order, $analysisResult);
            }
        }
    }
}
