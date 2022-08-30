<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Resource_Analysis_Result extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Resource initialization.
     */
    public function _construct()
    {
        $this->_init('postdirekt_addressfactory/analysis_result', 'order_address_id');
    }
}
