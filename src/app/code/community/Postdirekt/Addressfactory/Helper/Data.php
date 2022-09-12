<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Returns the module version.
     *
     * @return string
     */
    public function getModuleVersion(): string
    {
        $moduleName = $this->_getModuleName();

        return (string) Mage::getConfig()->getModuleConfig($moduleName)->version;
    }
}
