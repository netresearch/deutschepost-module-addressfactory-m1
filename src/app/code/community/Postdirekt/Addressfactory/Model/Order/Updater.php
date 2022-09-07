<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

use Postdirekt_Addressfactory_Model_Deliverability_Codes as DeliverabilityCodes;

class Postdirekt_Addressfactory_Model_Order_Updater
{
    /**
     * @var Postdirekt_Addressfactory_Model_Deliverability_Codes
     */
    protected $deliverabilityCodes;

    /**
     * @var Postdirekt_Addressfactory_Model_Order_StatusUpdater
     */
    protected $statusUpdater;

    /**
     * @var Postdirekt_Addressfactory_Model_Address_Updater
     */
    protected $addressUpdater;

    public function __construct()
    {
        $this->deliverabilityCodes = Mage::getSingleton('postdirekt_addressfactory/deliverability_codes');
        $this->statusUpdater = Mage::getSingleton('postdirekt_addressfactory/order_statusUpdater');
        $this->addressUpdater = Mage::getSingleton('postdirekt_addressfactory/address_updater');
    }


    /**
     * @param Mage_Sales_Model_Order $order
     * @param Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult
     * @return bool If Order was put on hold
     */
    public function holdIfNonDeliverable(
        Mage_Sales_Model_Order $order,
        Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult
    ): bool {
        if (!$order->canHold()) {
            return false;
        }
        $score = $this->deliverabilityCodes->computeScore($analysisResult->getStatusCodes());
        if ($score === DeliverabilityCodes::DELIVERABLE) {
            return false;
        }

        try {
            $order->hold();
            $order->save();
        } catch (Throwable $e) {
            return false;
        }

        return $order->getState() === Mage_Sales_Model_Order::STATE_HOLDED;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult
     * @return bool If Order was cancelled
     */
    public function cancelIfUndeliverable(
        Mage_Sales_Model_Order $order,
        Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult
    ): bool {
        if (!$order->canCancel()) {
            return false;
        }
        $score = $this->deliverabilityCodes->computeScore($analysisResult->getStatusCodes());
        if ($score !== DeliverabilityCodes::UNDELIVERABLE) {
            return false;
        }

        try {
            $order->cancel();
            $order->save();
        } catch (Throwable $e) {
            return false;
        }

        return $order->getState() === Mage_Sales_Model_Order::STATE_CANCELED;
    }


    /**
     * @param Mage_Sales_Model_Order $order
     * @param Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult
     * @return bool
     */
    public function updateShippingAddress(
        Mage_Sales_Model_Order $order,
        Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult
    ): bool {
        $wasUpdated = $this->addressUpdater->update($analysisResult, $order->getShippingAddress());
        if ($wasUpdated) {
            $this->statusUpdater->setStatusAddressCorrected((int)$order->getEntityId());
        }

        return $wasUpdated;
    }

}
