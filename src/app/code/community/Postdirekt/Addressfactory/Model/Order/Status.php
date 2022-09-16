<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Order_Status
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
     * @var Postdirekt_Addressfactory_Helper_Data
     */
    private $translator;

    public function __construct()
    {
        $this->translator = Mage::helper('postdirekt_addressfactory/data');
    }

    public function getStatusOptions(): array
    {
        return [
            self::NOT_ANALYSED => $this->translator->__('Not Checked'),
            self::PENDING => $this->translator->__('Pending'),
            self::UNDELIVERABLE => $this->translator->__('Undeliverable'),
            self::CORRECTION_REQUIRED => $this->translator->__('Correction Recommended'),
            self::POSSIBLY_DELIVERABLE => $this->translator->__('Possibly Deliverable'),
            self::DELIVERABLE => $this->translator->__('Deliverable'),
            self::ADDRESS_CORRECTED => $this->translator->__('Address Corrected'),
            self::MANUALLY_EDITED => $this->translator->__('Address Manually Edited'),
            self::ANALYSIS_FAILED => $this->translator->__('Analysis Failed'),
        ];
    }

    public function getStatusLabel(string $statusCode): string
    {
        $labels = $this->getStatusOptions();
        return $labels[$statusCode] ?? '';
    }
}
