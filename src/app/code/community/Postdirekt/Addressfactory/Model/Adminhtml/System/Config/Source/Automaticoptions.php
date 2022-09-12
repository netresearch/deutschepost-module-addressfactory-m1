<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Adminhtml_System_Config_Source_Automaticoptions
{
    const NO_AUTOMATIC_ANALYSIS = '1';
    const ANALYSIS_VIA_CRON = '2';
    const ON_ORDER_PLACE = '3';

    private $dataHelper;

    public function __construct()
    {
        $this->dataHelper = Mage::helper('postdirekt_addressfactory/data');
    }

    public function toOptionArray(): array
    {
        $optionArray = array();
        $options = $this->toArray();
        foreach ($options as $value => $label) {
            $optionArray[] = array('value' => $value, 'label' => $label);
        }

        return $optionArray;
    }

    public function toArray(): array
    {
        return array(
            self::NO_AUTOMATIC_ANALYSIS => $this->dataHelper->__('No Automatic Analysis'),
            self::ANALYSIS_VIA_CRON => $this->dataHelper->__('Analysis via Cron'),
            self::ON_ORDER_PLACE => $this->dataHelper->__('Analysis on Order placement')
        );
    }
}
