<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

use Postdirekt_Addressfactory_Model_Deliverability_Codes as DeliverabilityCodes;

class Postdirekt_Addressfactory_Model_Order_Analysis
{
    /**
     * @var Postdirekt_Addressfactory_Model_Service_AddressAnalysis
     */
    protected $addressAnalysisService;

    /**
     * @var Postdirekt_Addressfactory_Model_Deliverability_Codes
     */
    protected $deliverabilityScoreService;

    /**
     * @var Postdirekt_Addressfactory_Model_Order_StatusUpdater
     */
    protected $statusUpdater;

    public function __construct()
    {
        $this->addressAnalysisService = Mage::getSingleton('postdirekt_addressfactory/service_addressAnalysis');
        $this->deliverabilityScoreService = Mage::getSingleton('postdirekt_addressfactory/deliverability_codes');
        $this->statusUpdater = Mage::getSingleton('postdirekt_addressfactory/order_statusUpdater');
    }

    /**
     * Get ADDRESSFACTORY DIRECT Deliverability analysis objects
     * for the Shipping Address of every given Order.
     *
     * @param Mage_Sales_Model_Order[] $orders
     * @return Postdirekt_Addressfactory_Model_Analysis_Result[] Dictionary: [(int) $order->getEntityId() => AnalysisResult]
     */
    public function analyse(array $orders): array
    {
        $addresses = [];
        foreach ($orders as $order) {
            $addresses[] = $order->getShippingAddress();
        }

        try {
            $analysisResults = $this->addressAnalysisService->analyse($addresses);
        } catch (RuntimeException $exception) {
            $analysisResults = [];
        }
        $result = [];
        foreach ($orders as $order) {
            $analysisResult = $analysisResults[(int)$order->getShippingAddress()->getEntityId()] ?? null;
            $this->updateDeliverabilityStatus((int)$order->getId(), $analysisResult);
            $result[$order->getEntityId()] = $analysisResult;
        }

        return $result;
    }

    /**
     * @param int $orderId
     * @param Postdirekt_Addressfactory_Model_Analysis_Result|null $analysisResult
     * @return void
     */
    private function updateDeliverabilityStatus(int $orderId, $analysisResult)
    {
        if (!$analysisResult) {
            $this->statusUpdater->setStatusAnalysisFailed($orderId);
            return;
        }

        $currentStatus = $this->statusUpdater->getStatus($orderId);
        $statusCode = $this->deliverabilityScoreService->computeScore(
            $analysisResult->getStatusCodes(),
            $currentStatus === Postdirekt_Addressfactory_Model_Order_Status::ADDRESS_CORRECTED
        );
        switch ($statusCode) {
            case DeliverabilityCodes::DELIVERABLE:
                $this->statusUpdater->setStatusDeliverable($orderId);
                break;
            case DeliverabilityCodes::POSSIBLY_DELIVERABLE:
                $this->statusUpdater->setStatusPossiblyDeliverable($orderId);
                break;
            case DeliverabilityCodes::UNDELIVERABLE:
                $this->statusUpdater->setStatusUndeliverable($orderId);
                break;
            case DeliverabilityCodes::CORRECTION_REQUIRED:
                $this->statusUpdater->setStatusCorrectionRequired($orderId);
        }
    }
}
