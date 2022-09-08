<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Observer_PlaceOrder
{
    /**
     * @var Postdirekt_Addressfactory_Model_Config
     */
    private $config;

    public function __construct()
    {
        $this->config = Mage::getSingleton('postdirekt_addressfactory/config');
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
}
