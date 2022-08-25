<?php

/**
 * See LICENSE.md for license details.
 */

class Postdirekt_Addressfactory_Model_Adminhtml_System_Config_Source_Yesoptno
{
    const N   = '0';
    const Y   = '1';
    const OPT = '2';

    /**
     * Options getter
     */
    public function toOptionArray(): array
    {
        $options = $this->toArray();
        $optionsArray = array();
        foreach (array(self::Y, self::OPT, self::N) as $optionValue) {
            $optionsArray[] = array('value' => $optionValue, 'label' => $options[$optionValue]);
        }

        return $optionsArray;
    }

    /**
     * Get options in "key-value" format
     */
    public function toArray(): array
    {
        return array(
            self::N => Mage::helper('adminhtml')->__('Disable'),
            self::Y => Mage::helper('adminhtml')->__('Enable'),
            self::OPT => Mage::helper('adminhtml')->__('Enable on customers choice'),
        );
    }
}
