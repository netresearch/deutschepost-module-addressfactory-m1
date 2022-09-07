<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Deliverability_Codes
{
    const DELIVERABLE = 'deliverable';
    const UNDELIVERABLE = 'undeliverable';
    const POSSIBLY_DELIVERABLE = 'possibly_deliverable';
    const CORRECTION_REQUIRED = 'correction_required';

    const PERSON_DELIVERABLE = 'PDC050105';
    const PERSON_NOT_DELIVERABLE = 'PDC050106';
    const HOUSEHOLD_DELIVERABLE = 'PDC040105';
    const HOUSEHOLD_UNDELIVERABLE = 'PDC040106';
    const BUILDING_DELIVERABLE = 'PDC030105';
    const PERSON_NOT_MATCHED = 'PDC050500';
    const HOUSEHOLD_NOT_MATCHED = 'PDC040500';
    const BUILDING_UNDELIVERABLE = 'PDC030106';
    const NOT_CORRECTABLE = 'BAC000111';
    const HOUSE_NUMBER_NOT_FILLED = 'FNC030501';

    const STATUS_CODES_SIGNIFICANTLY_CORRECTED = ['103', '108'];

    /**
     * @var Postdirekt_Addressfactory_Helper_Data
     */
    private $translator;

    public function __construct()
    {
        $this->translator = Mage::helper('postdirekt_addressfactory/data');
    }

    /**
     * @param string[] $codes
     * @param bool $wasAlreadyUpdated
     * @return string
     */
    public function computeScore(array $codes, bool $wasAlreadyUpdated = false): string
    {
        $codes = $this->filterInapplicable($codes);

        if (!$wasAlreadyUpdated) {
            foreach ($codes as $code) {
                $statusCode = substr($code, -3, 3);
                if (in_array($statusCode, self::STATUS_CODES_SIGNIFICANTLY_CORRECTED, true)) {
                    return self::CORRECTION_REQUIRED;
                }
            }
        }

        if (\in_array(self::HOUSE_NUMBER_NOT_FILLED, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::NOT_CORRECTABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::PERSON_DELIVERABLE, $codes, true)) {
            return self::DELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_DELIVERABLE, $codes, true) &&
            \in_array(self::HOUSEHOLD_DELIVERABLE, $codes, true)) {
            return self::DELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_DELIVERABLE, $codes, true) &&
            \in_array(self::HOUSEHOLD_UNDELIVERABLE, $codes, true) &&
            !\in_array(self::BUILDING_DELIVERABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_MATCHED, $codes, true) &&
            \in_array(self::HOUSEHOLD_DELIVERABLE, $codes, true)) {
            return self::DELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_MATCHED, $codes, true) &&
            \in_array(self::HOUSEHOLD_UNDELIVERABLE, $codes, true) &&
            \in_array(self::BUILDING_DELIVERABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_MATCHED, $codes, true) &&
            \in_array(self::HOUSEHOLD_NOT_MATCHED, $codes, true) &&
            \in_array(self::BUILDING_DELIVERABLE, $codes, true)) {
            return self::POSSIBLY_DELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_MATCHED, $codes, true) &&
            \in_array(self::HOUSEHOLD_NOT_MATCHED, $codes, true) &&
            \in_array(self::BUILDING_UNDELIVERABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_DELIVERABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        return self::POSSIBLY_DELIVERABLE;
    }

    /**
     * @param string[] $codes
     * @return string[][]
     */
    public function getLabels(array $codes): array
    {
        $mappedCodes = [
            self::NOT_CORRECTABLE => [
                'icon' => 'icon-alert',
                'label' => $this->translator->__('Address not valid'),
                'code' => self::NOT_CORRECTABLE,
            ],
            'FNC000500' => [
                'icon' => 'icon-alert',
                'label' => $this->translator->__('Not found in reference'),
                'code' => 'FNC000500',
            ]
        ];

        $mappedModuleCodes = [
            'BAC' => '',
            'FNC' => '',
        ];

        $mappedFieldCodes = [
            '000' => '',
            '010' => $this->translator->__('House address'),
            '012' => $this->translator->__('Bulk recipient address'),
            '020' => $this->translator->__('Street'),
            '030' => $this->translator->__('House number'), // "Building"
            '040' => $this->translator->__('Household'),
            '050' => $this->translator->__('Person'),
            '060' => $this->translator->__('Postal code'),
            '100' => $this->translator->__('Postal code'),
            '101' => $this->translator->__('City'),
            '102' => $this->translator->__('Street'),
            '103' => $this->translator->__('City addition'),
            '105' => $this->translator->__('City'),
            '106' => $this->translator->__('Street'),
            '110' => $this->translator->__('Postal code'),
            '111' => $this->translator->__('City'),
            '113' => $this->translator->__('City addition'),
            '115' => $this->translator->__('City'),
            '120' => $this->translator->__('Postal code'),
            '121' => $this->translator->__('City'),
            '122' => $this->translator->__('Bulk receiver name'),
            '123' => $this->translator->__('City addition'),
            '125' => $this->translator->__('City'),
            '130' => $this->translator->__('Postal code'),
            '131' => $this->translator->__('City'),
            '133' => $this->translator->__('City addition'),
            '135' => $this->translator->__('City'),
            '144' => $this->translator->__('Country'),
            '170' => $this->translator->__('Postal code'),
            '171' => $this->translator->__('City'),
            '173' => $this->translator->__('City addition'),
//            '011' => $this->translator->__('Post box address'),
//            '013' => $this->translator->__('Parcel station address'),
//            '017' => $this->translator->__('Post office address'),
//            '104' => $this->translator->__('District'),
//            '112' => $this->translator->__('Post box number'),
//            '132' => $this->translator->__('Post box number'),
//            '140' => $this->translator->__('Political information'),
//            '145' => $this->translator->__('Route code'),
//            '150' => $this->translator->__('Political information'),
//            '154' => $this->translator->__('Route code'),
//            '160' => $this->translator->__('Political information'),
//            '164' => $this->translator->__('Route code'),
//            '172' => $this->translator->__('Post office number'),
//            '200' => $this->translator->__('House number'),
//            '201' => $this->translator->__('House number addition'),
        ];

        $mappedStatusCodes = [
            '103' => $this->translator->__('corrected significantly'),
            '104' => $this->translator->__('corrected marginally'),
            '106' => $this->translator->__('undeliverable'),
            '108' => $this->translator->__('incorporated or renamed'),
            '111' => $this->translator->__('different'),
            '112' => $this->translator->__('moved'),
            '113' => $this->translator->__('address type changed'),
            '120' => $this->translator->__('receiver deceased'),
            '121' => $this->translator->__('reportedly deceased'),
            '140' => $this->translator->__('matched in Robinson list'),
            '141' => $this->translator->__('matched in fake-name list'),
            '500' => $this->translator->__('not found'), // "not matched"
            '501' => $this->translator->__('not filled'),
            '503' => $this->translator->__('ambiguous'),
            '504' => $this->translator->__('is foreign address'),
            '505' => $this->translator->__('incorporated'),
            '506' => $this->translator->__('is company address'),
//            '107' => $this->translator->__('enriched'),
//            '105' => $this->translator->__('deliverable'),
//            '509' => $this->translator->__('not queried'),
//            '102' => $this->translator->__('correct'),
//            '110' => $this->translator->__('separated from original data'),
//            '130' => $this->translator->__('doublet'),
//            '131' => $this->translator->__('head doublet'),
//            '132' => $this->translator->__('followed doublet'),
//            '135' => $this->translator->__('followed doublet in negative list'),
        ];

        $labels = [];
        // remove redundant codes
        $codes = $this->filterInapplicable($codes);

        foreach ($codes as $code) {
            if (isset($mappedCodes[$code])) {
                $labels[] = $mappedCodes[$code];
                continue;
            }

            $moduleCode = substr($code, 0, 3);
            $fieldCode = substr($code, -6, 3);
            $statusCode = substr($code, -3, 3);

            if (isset($mappedModuleCodes[$moduleCode], $mappedFieldCodes[$fieldCode], $mappedStatusCodes[$statusCode])) {
                $iconCode = $this->mapToIcon($fieldCode);
                $label = ucfirst(
                    trim(
                        $mappedModuleCodes[$moduleCode] . ' '
                        . $mappedFieldCodes[$fieldCode] . ' '
                        . $mappedStatusCodes[$statusCode]
                    )
                );
                $labels[] = [
                    'icon' => $iconCode,
                    'label' => $label,
                    'code' => $code,
                ];
            }
        }

        return $labels;
    }

    /**
     * @param string[] $codes
     * @return string[]
     */
    private function filterInapplicable(array $codes): array
    {
        /**
         * BAC201110 - House numbers can be separated by the API, but Magento cannot take advantage of this
         * BAC010103, BAC010104, BAC010500 - These are always explained in more detail by another code.
         * FNC201103 - Street number addition corrected: This is a false positive in connection with BAC201110
         */
        $inapplicable = ['BAC201110', 'BAC010103', 'BAC010500', 'BAC010104', 'FNC201103'];
        $codes = array_diff($codes, $inapplicable);
        if (\in_array(self::NOT_CORRECTABLE, $codes, true)) {
            /**
             * If BAC000111 (not correctable) is set, all other analysis modules become irrelevant
             */
            $codes = array_filter($codes, static function ($key) {
                return strpos($key, 'BAC') !== false;
            });
        }

        return $codes;
    }

    private function mapToIcon(string $fieldCode): string
    {
        $inHouse = ['010', '012', '030'];

        switch ($fieldCode) {
            case '000':
                $iconCode = 'icon-alert';
                break;
            case in_array($fieldCode, $inHouse, true):
                $iconCode = 'icon-house';
                break;
            case '050':
                $iconCode = 'icon-user-account';
                break;
            case '040':
                $iconCode = 'icon-user-group';
                break;
            default:
                $iconCode = 'icon-info';
        }
        return $iconCode;
    }
}
