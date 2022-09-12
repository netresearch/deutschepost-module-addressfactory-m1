<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Observer_UpdateAddress
{
    /**
     * @var Postdirekt_Addressfactory_Model_Address_Updater
     */
    private $addressUpdater;

    /**
     * @var Postdirekt_Addressfactory_Model_Order_StatusUpdater
     */
    private $statusUpdater;

    /**
     * @var Postdirekt_Addressfactory_Model_Logger
     */
    private $logger;

    public function __construct()
    {
        $this->addressUpdater = Mage::getSingleton('postdirekt_addressfactory/address_updater');
        $this->statusUpdater = Mage::getSingleton('postdirekt_addressfactory/order_statusUpdater');
        $this->logger = Mage::getModel('postdirekt_addressfactory/logger');
    }

    /**
     * Discard analysis result if the shipping address changed.
     *
     * The outcome of a shipping address check is persisted as analysis
     * result entity. When the shipping address gets edited, then the
     * analysis result does no longer apply. The address check must be
     * performed again based on the updated address. To enable this
     * behavior, the now invalid analysis result gets deleted. There is,
     * however, one exception: When the shipping address gets updated
     * to match the address suggestion (manually or applied), then the
     * updated shipping address still matches the analysis result and
     * nothing needs to be done.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function handleManualUpdate(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = $observer->getData('address');
        if ($address->getAddressType() !== 'shipping') {
            // no action on billing addresses
            return;
        }

        $diff = array_diff($address->getOrigData(), $address->getData());
        if (empty($diff)) {
            // address saved but unchanged, ignore.
            return;
        }

        $order = $address->getOrder();
        if ($order->getIsVirtual()) {
            // no delivery for virtual orders
            return;
        }

        /** @var Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult */
        $analysisResult = Mage::getModel('postdirekt_addressfactory/analysis_result')->load($address->getId());
        if (!$analysisResult->getId()) {
            // address not (yet) analysed, no action
            return;
        }

        try {
            if ($this->addressUpdater->addressesAreDifferent($analysisResult, $address)) {
                $this->statusUpdater->setStatusManuallyEdited((int) $order->getId());
                $analysisResult->delete();
            } else {
                $this->statusUpdater->setStatusAddressCorrected((int) $order->getId());
            }
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }
    }
}
