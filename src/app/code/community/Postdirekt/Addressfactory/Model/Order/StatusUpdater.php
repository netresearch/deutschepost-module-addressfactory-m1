<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class Postdirekt_Addressfactory_Model_Order_StatusUpdater
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->logger = Mage::getModel('postdirekt_addressfactory/logger');
    }

    /**
     * Update analysis status in persistent storage and sales order grid.
     */
    private function updateStatus(int $orderId, string $status): bool
    {
        /** @var Postdirekt_Addressfactory_Model_Analysis_Status $analysisStatus */
        $analysisStatus = Mage::getModel('postdirekt_addressfactory/analysis_status');
        $analysisStatus->setOrderId($orderId)
            ->setStatus($status);
        try {
            $analysisStatus->save();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return false;
        }

        return true;
    }

    public function setStatusPending(int $orderId): bool
    {
        return $this->updateStatus($orderId, Postdirekt_Addressfactory_Model_Order_Status::PENDING);
    }

    public function setStatusUndeliverable(int $orderId): bool
    {
        return $this->updateStatus($orderId, Postdirekt_Addressfactory_Model_Order_Status::UNDELIVERABLE);
    }

    public function setStatusCorrectionRequired(int $orderId): bool
    {
        return $this->updateStatus($orderId, Postdirekt_Addressfactory_Model_Order_Status::CORRECTION_REQUIRED);
    }

    public function setStatusPossiblyDeliverable(int $orderId): bool
    {
        return $this->updateStatus($orderId, Postdirekt_Addressfactory_Model_Order_Status::POSSIBLY_DELIVERABLE);
    }

    public function setStatusDeliverable(int $orderId): bool
    {
        return $this->updateStatus($orderId, Postdirekt_Addressfactory_Model_Order_Status::DELIVERABLE);
    }

    public function setStatusAddressCorrected(int $orderId): bool
    {
        return $this->updateStatus($orderId, Postdirekt_Addressfactory_Model_Order_Status::ADDRESS_CORRECTED);
    }

    public function setStatusAnalysisFailed(int $orderId): bool
    {
        return $this->updateStatus($orderId, Postdirekt_Addressfactory_Model_Order_Status::ANALYSIS_FAILED);
    }

    public function setStatusManuallyEdited(int $orderId): bool
    {
        return $this->updateStatus($orderId, Postdirekt_Addressfactory_Model_Order_Status::MANUALLY_EDITED);
    }

    public function getStatus(int $orderId): string
    {
        try {
            /** @var Postdirekt_Addressfactory_Model_Analysis_Status $deliverabilityStatus */
            $deliverabilityStatus = Mage::getModel('postdirekt_addressfactory/analysis_status')->load($orderId);
        } catch (Exception $exception) {
            return Postdirekt_Addressfactory_Model_Order_Status::NOT_ANALYSED;
        }

        return $deliverabilityStatus->getStatus();
    }

    public function isStatusCorrectable(string $status): bool
    {
        $correctableStatuses = [
            Postdirekt_Addressfactory_Model_Order_Status::ANALYSIS_FAILED,
            Postdirekt_Addressfactory_Model_Order_Status::UNDELIVERABLE,
            Postdirekt_Addressfactory_Model_Order_Status::POSSIBLY_DELIVERABLE,
            Postdirekt_Addressfactory_Model_Order_Status::DELIVERABLE,
            Postdirekt_Addressfactory_Model_Order_Status::CORRECTION_REQUIRED,
            Postdirekt_Addressfactory_Model_Order_Status::MANUALLY_EDITED
        ];

        return \in_array($status, $correctableStatuses, true);
    }
}
