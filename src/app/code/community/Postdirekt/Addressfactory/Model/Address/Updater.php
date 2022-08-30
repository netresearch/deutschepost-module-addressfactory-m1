<?php
/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Address_Updater
{
    /**
     * Overwrite the given Order Address with data from an ADDRESSFACTORY DIRECT deliverability analysis.
     *
     * The only address fields that will be modified are:
     * - first name
     * - last name
     * - street
     * - city
     * - postal code
     *
     * Note: Do not use this method directly when operating on Order scope,
     * use Postdirekt_Addressfactory_Model_Order_Updater::updateShippingAddress instead
     * to keep the Order's analysis status in sync.
     *
     * @param Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult
     * @param Mage_Sales_Model_Order_Address|null $address
     * @return bool If the address update was successful
     */
    public function update(
        Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult,
        Mage_Sales_Model_Order_Address $address = null
    ): bool {
        if ($address === null) {
            try {
                $address = Mage::getModel('sales/order_address')->load($analysisResult->getOrderAddressId());
            } catch (Throwable $exception) {
                return false;
            }
        }

        if (!$this->addressesAreDifferent($analysisResult, $address)) {
            return false;
        }

        $street = implode(' ', [$analysisResult->getStreet(), $analysisResult->getStreetNumber()]);
        $address->setStreet($street);
        $address->setFirstname($analysisResult->getFirstName());
        $address->setLastname($analysisResult->getLastName());
        $address->setPostcode($analysisResult->getPostalCode());
        $address->setCity($analysisResult->getCity());

        try {
            $address->save();
        } catch (Throwable $exception) {
            return false;
        }

        return true;
    }

    public function addressesAreDifferent(
        Postdirekt_Addressfactory_Model_Analysis_Result $analysisResult,
        Mage_Sales_Model_Order_Address $orderAddress
    ): bool {
        $street = trim(implode(' ', [$analysisResult->getStreet(), $analysisResult->getStreetNumber()]));
        $orderStreet = trim(implode('', $orderAddress->getStreet()));

        return ($orderAddress->getFirstname() !== $analysisResult->getFirstName() ||
            $orderAddress->getLastname() !== $analysisResult->getLastName() ||
            $orderAddress->getCity() !== $analysisResult->getCity() ||
            $orderAddress->getPostcode() !== $analysisResult->getPostalCode() ||
            $street !== $orderStreet);
    }
}
