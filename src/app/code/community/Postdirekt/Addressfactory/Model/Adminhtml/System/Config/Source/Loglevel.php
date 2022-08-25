<?php

/**
 * See LICENSE.md for license details.
 */

class Postdirekt_Addressfactory_Model_Adminhtml_System_Config_Source_Loglevel
{
    /**
     * Options getter
     */
    public function toOptionArray(): array
    {
        $optionArray = array();

        $options = $this->toArray();
        foreach ($options as $value => $label) {
            $optionArray[]= array('value' => $value, 'label' => $label);
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     */
    public function toArray(): array
    {
        return array(
            Zend_Log::ERR   => Mage::helper('postdirekt_addressfactory/data')->__('Error'),
            Zend_Log::WARN  => Mage::helper('postdirekt_addressfactory/data')->__('Warning'),
            Zend_Log::DEBUG => Mage::helper('postdirekt_addressfactory/data')->__('Debug')
        );
    }
}
