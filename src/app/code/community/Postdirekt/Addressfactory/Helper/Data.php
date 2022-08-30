<?php
/**
 * See LICENSE.md for license details.
 */

class Postdirekt_Addressfactory_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Returns the module version.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        $moduleName = $this->_getModuleName();

        return (string) Mage::getConfig()->getModuleConfig($moduleName)->version;
    }
}
