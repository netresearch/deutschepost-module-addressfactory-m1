<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Analysis_Result extends Mage_Core_Model_Abstract
{
    const ORDER_ADDRESS_ID = 'order_address_id';
    const STATUS_CODE = 'status_codes';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const CITY = 'city';
    const POSTAL_CODE = 'postal_code';
    const STREET = 'street';
    const STREET_NUMBER = 'street_number';

    /**
     * Initialize AnalysisResult resource model.
     */
    public function _construct()
    {
        $this->_init('postdirekt_addressfactory/analysis_result');

        parent::_construct();
    }

    /**
     * @param int $orderAddressId
     */
    public function setOrderAddressId(int $orderAddressId)
    {
        $this->setData(self::ORDER_ADDRESS_ID, $orderAddressId);
    }

    /**
     * @return int
     */
    public function getOrderAddressId(): int
    {
        return (int)$this->getData(self::ORDER_ADDRESS_ID);
    }

    /**
     * @param string $statusCodes
     */
    public function setStatusCodes(string $statusCodes)
    {
        $this->setData(self::STATUS_CODE, $statusCodes);
    }

    /**
     * @return string[]
     */
    public function getStatusCodes(): array
    {
        $result = [];
        if ($this->getData(self::STATUS_CODE)) {
            $result = explode(',', $this->getData(self::STATUS_CODE));
        }

        return $result;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName)
    {
        $this->setData(self::FIRST_NAME, $firstName);
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return (string) $this->getData(self::FIRST_NAME);
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName)
    {
        $this->setData(self::LAST_NAME, $lastName);
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return (string) $this->getData(self::LAST_NAME);
    }

    /**
     * @param string $city
     */
    public function setCity(string $city)
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return (string) $this->getData(self::CITY);
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode(string $postalCode)
    {
        $this->setData(self::POSTAL_CODE, $postalCode);
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return (string) $this->getData(self::POSTAL_CODE);
    }

    /**
     * @param string $street
     * @return void
     */
    public function setStreet(string $street)
    {
        $this->setData(self::STREET, $street);
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return (string) $this->getData(self::STREET);
    }

    /**
     * @param string $streetNumber
     */
    public function setStreetNumber(string $streetNumber)
    {
        $this->setData(self::STREET_NUMBER, $streetNumber);
    }

    /**
     * @return string
     */
    public function getStreetNumber(): string
    {
        return (string) $this->getData(self::STREET_NUMBER);
    }
}
