<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Config
{
    const CONFIG_XML_FIELD_ENABLED = 'customer/postdirekt_addressfactory/active';
    const CONFIG_XML_FIELD_API_USER = 'customer/postdirekt_addressfactory/api_user';
    const CONFIG_XML_FIELD_API_PASSWORD = 'customer/postdirekt_addressfactory/api_password';
    const CONFIG_XML_FIELD_CONFIGURATION_NAME = 'customer/postdirekt_addressfactory/configuration_name';
    const CONFIG_XML_FIELD_MANDATE_NAME = 'customer/postdirekt_addressfactory/mandate_name';
    const CONFIG_XML_FIELD_LOGGING_ENABLED = 'customer/postdirekt_addressfactory/logging_enabled';
    const CONFIG_XML_FIELD_LOG_LEVEL = 'customer/postdirekt_addressfactory/log_level';
    const CONFIG_XML_FIELD_HOLD_NON_DELIVERABLE_ORDERS = 'customer/postdirekt_addressfactory/hold_non_deliverable_orders';
    const CONFIG_XML_FIELD_AUTO_CANCEL_ORDERS = 'customer/postdirekt_addressfactory/auto_cancel_orders';
    const CONFIG_XML_FIELD_AUTO_UPDATE_SHIPPING_ADDRESS = 'customer/postdirekt_addressfactory/auto_update_shipping_address';
    const CONFIG_XML_FIELD_AUTOMATIC_ADDRESS_ANALYSIS = 'customer/postdirekt_addressfactory/automatic_address_analysis';
    const CONFIG_XML_FIELD_AUTO_VALIDATE_MANUAL_EDITED = 'customer/postdirekt_addressfactory/auto_validate_manual_edited';

    public function isActive($store = null): bool
    {
        return Mage::getStoreConfigFlag(self::CONFIG_XML_FIELD_ENABLED, $store);
    }

    public function getApiUser($store = null): string
    {
        return (string)Mage::getStoreConfig(self::CONFIG_XML_FIELD_API_USER, $store);
    }

    public function getApiPassword($store = null): string
    {
        return (string)Mage::getStoreConfig(self::CONFIG_XML_FIELD_API_PASSWORD, $store);
    }

    public function getConfigurationName($store = null): string
    {
        return (string)Mage::getStoreConfig(self::CONFIG_XML_FIELD_CONFIGURATION_NAME, $store);
    }

    public function getMandateName($store = null): string
    {
        return (string)Mage::getStoreConfig(self::CONFIG_XML_FIELD_MANDATE_NAME, $store);
    }

    public function isLoggingEnabled($store = null): bool
    {
        return Mage::getStoreConfigFlag(self::CONFIG_XML_FIELD_LOGGING_ENABLED, $store);
    }

    public function getLogLevel($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_FIELD_LOG_LEVEL, $store);
    }

    public function isHoldNonDeliverableOrders(): bool
    {
        return Mage::getStoreConfigFlag(self::CONFIG_XML_FIELD_HOLD_NON_DELIVERABLE_ORDERS);
    }

    public function isAutoCancelOrders(): bool
    {
        return Mage::getStoreConfigFlag(self::CONFIG_XML_FIELD_AUTO_CANCEL_ORDERS);
    }

    public function isAutoUpdateShippingAddress(): bool
    {
        return Mage::getStoreConfigFlag(self::CONFIG_XML_FIELD_AUTO_UPDATE_SHIPPING_ADDRESS);
    }

    protected function getAutomaticAddressAnalysis(): string
    {
        return (string) Mage::getStoreConfig(self::CONFIG_XML_FIELD_AUTOMATIC_ADDRESS_ANALYSIS);
    }

    public function isManualAnalysisOnly(): bool
    {
        return $this->getAutomaticAddressAnalysis() === Postdirekt_Addressfactory_Model_Adminhtml_System_Config_Source_Automaticoptions::NO_AUTOMATIC_ANALYSIS;
    }

    public function isAnalysisOnOrderPlace(): bool
    {
        return $this->getAutomaticAddressAnalysis() === Postdirekt_Addressfactory_Model_Adminhtml_System_Config_Source_Automaticoptions::ON_ORDER_PLACE;
    }

    public function isAutomaticAddressAnalysis(): bool
    {
        return $this->getAutomaticAddressAnalysis() === Postdirekt_Addressfactory_Model_Adminhtml_System_Config_Source_Automaticoptions::ANALYSIS_VIA_CRON;
    }

    public function isAutoValidateManualEdited(): bool
    {
        return Mage::getStoreConfigFlag(self::CONFIG_XML_FIELD_AUTO_VALIDATE_MANUAL_EDITED);
    }
}
