<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Model_Analysis_Status extends Mage_Core_Model_Abstract
{
    const ORDER_ID = 'order_id';
    const STATUS = 'status';

    protected function _construct()
    {
        $this->_init('postdirekt_addressfactory/analysis_status');
        parent::_construct();
    }

    /**
     * @param int $orderId
     */
    public function setOrderId(int $orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return int
     */
    public function getOrderId():int
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return (string) $this->getData(self::STATUS);
    }
}
