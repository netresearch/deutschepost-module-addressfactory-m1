<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

use PostDirekt\Sdk\AddressfactoryDirect\Api\Data\RecordInterface;
use Postdirekt_Addressfactory_Model_Analysis_Result as AnalysisResult;

class Postdirekt_Addressfactory_Model_Analysis_ResponseMapper
{
    const PARCELSTATION = 'Packstation';
    const POSTSTATION = 'Postfiliale';
    const POSTFACH = 'Postfach';

    /**
     * @var Postdirekt_Addressfactory_Model_Analysis_CodeFilter
     */
    protected $codeFilter;

    /**
     * @param Postdirekt_Addressfactory_Model_Analysis_CodeFilter $codeFilter
     */
    public function __construct()
    {
        $this->codeFilter = Mage::getSingleton('postdirekt_addressfactory/analysis_codeFilter');
    }

    /**
     * @param RecordInterface[] $records
     * @return AnalysisResult[]
     */
    public function mapRecordsResponse(array $records): array
    {
        $newAnalysisResults = [];
        foreach ($records as $record) {
            $statusCodes = $this->codeFilter->filterCodes($record);

            $data = $this->mapAddressTypes($record);

            /** @var AnalysisResult $newAnalysisResult */
            $newAnalysisResult = Mage::getModel('postdirekt_addressfactory/analysis_result');
            $newAnalysisResult->setOrderAddressId($record->getRecordId());
            $newAnalysisResult->setStatusCodes(implode(',', $statusCodes));
            $newAnalysisResult->setFirstName($record->getPerson() ? $record->getPerson()->getFirstName() : '');
            $newAnalysisResult->setLastName($record->getPerson() ? $record->getPerson()->getLastName() : '');
            $newAnalysisResult->setCity($data[AnalysisResult::CITY]);
            $newAnalysisResult->setPostalCode($data[AnalysisResult::POSTAL_CODE]);
            $newAnalysisResult->setStreet($data[AnalysisResult::STREET]);
            $newAnalysisResult->setStreetNumber($data[AnalysisResult::STREET_NUMBER]);

            $newAnalysisResults[$newAnalysisResult->getOrderAddressId()] = $newAnalysisResult;
        }

        return $newAnalysisResults;
    }

    /**
     * @return string[]
     */
    private function mapAddressTypes(RecordInterface $record): array
    {
        $data = [];
        if ($record->getAddress()) {
            $data[AnalysisResult::POSTAL_CODE] = $record->getAddress()->getPostalCode();
            $data[AnalysisResult::CITY] = $record->getAddress()->getCity();
            $data[AnalysisResult::STREET] = $record->getAddress()->getStreetName();
            $data[AnalysisResult::STREET_NUMBER] = trim(
                implode(' ', [
                    $record->getAddress()->getStreetNumber(),
                    $record->getAddress()->getStreetNumberAddition(),
                ])
            );
        }

        if ($record->getParcelStation()) {
            $data[AnalysisResult::POSTAL_CODE] = $record->getParcelStation()->getPostalCode();
            $data[AnalysisResult::CITY] = $record->getParcelStation()->getCity();
            $data[AnalysisResult::STREET] = self::PARCELSTATION;
            $data[AnalysisResult::STREET_NUMBER] = $record->getParcelStation()->getNumber();
        }

        if ($record->getPostOffice()) {
            $data[AnalysisResult::POSTAL_CODE] = $record->getPostOffice()->getPostalCode();
            $data[AnalysisResult::CITY] = $record->getPostOffice()->getCity();
            $data[AnalysisResult::STREET] = self::POSTSTATION;
            $data[AnalysisResult::STREET_NUMBER] = $record->getPostOffice()->getNumber();
        }

        if ($record->getPostalBox()) {
            $data[AnalysisResult::POSTAL_CODE] = $record->getPostalBox()->getPostalCode();
            $data[AnalysisResult::CITY] = $record->getPostalBox()->getCity();
            $data[AnalysisResult::STREET] = self::POSTFACH;
            $data[AnalysisResult::STREET_NUMBER] = $record->getPostalBox()->getNumber();
        }

        if ($record->getBulkReceiver()) {
            $data[AnalysisResult::POSTAL_CODE] = $record->getBulkReceiver()->getPostalCode();
            $data[AnalysisResult::CITY] = $record->getBulkReceiver()->getCity();
            $data[AnalysisResult::STREET] = $record->getBulkReceiver()->getName();
            $data[AnalysisResult::STREET_NUMBER] = '';
        }

        return $data;
    }
}
