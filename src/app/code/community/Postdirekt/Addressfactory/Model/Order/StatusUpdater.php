<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class Postdirekt_Addressfactory_Model_Order_StatusUpdater
{
    const NOT_ANALYSED = 'not_analysed';
    const PENDING = 'pending';
    const UNDELIVERABLE = 'undeliverable';
    const CORRECTION_REQUIRED = 'correction_required';
    const POSSIBLY_DELIVERABLE = 'possibly_deliverable';
    const DELIVERABLE = 'deliverable';
    const ADDRESS_CORRECTED = 'address_corrected';
    const ANALYSIS_FAILED = 'analysis_failed';
    const MANUALLY_EDITED = 'manually_edited';

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
        return $this->updateStatus($orderId, self::PENDING);
    }

    public function setStatusUndeliverable(int $orderId): bool
    {
        return $this->updateStatus($orderId, self::UNDELIVERABLE);
    }

    public function setStatusCorrectionRequired(int $orderId): bool
    {
        return $this->updateStatus($orderId, self::CORRECTION_REQUIRED);
    }

    public function setStatusPossiblyDeliverable(int $orderId): bool
    {
        return $this->updateStatus($orderId, self::POSSIBLY_DELIVERABLE);
    }

    public function setStatusDeliverable(int $orderId): bool
    {
        return $this->updateStatus($orderId, self::DELIVERABLE);
    }

    public function setStatusAddressCorrected(int $orderId): bool
    {
        return $this->updateStatus($orderId, self::ADDRESS_CORRECTED);
    }

    public function setStatusAnalysisFailed(int $orderId): bool
    {
        return $this->updateStatus($orderId, self::ANALYSIS_FAILED);
    }

    public function setStatusManuallyEdited(int $orderId): bool
    {
        return $this->updateStatus($orderId, self::MANUALLY_EDITED);
    }

    public function getStatus(int $orderId): string
    {
        try {
            /** @var Postdirekt_Addressfactory_Model_Analysis_Status $deliverabilityStatus */
            $deliverabilityStatus = Mage::getModel('postdirekt_addressfactory/analysis_status')->load($orderId);
        } catch (Exception $exception) {
            return self::NOT_ANALYSED;
        }

        return $deliverabilityStatus->getStatus();
    }

    public function isStatusCorrectable(string $status): bool
    {
        $correctableStatuses = [
            self::ANALYSIS_FAILED,
            self::UNDELIVERABLE,
            self::POSSIBLY_DELIVERABLE,
            self::DELIVERABLE,
            self::CORRECTION_REQUIRED,
            self::MANUALLY_EDITED
        ];

        return \in_array($status, $correctableStatuses, true);
    }
}
