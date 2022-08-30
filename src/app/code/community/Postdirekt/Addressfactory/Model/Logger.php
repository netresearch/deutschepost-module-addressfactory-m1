<?php

/**
 * See LICENSE.md for license details.
 */

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Postdirekt_Addressfactory_Model_Logger extends AbstractLogger
{
    const PROPERTY_SCOPE = 'scope';

    const LOG_FILE = 'postdirekt_addressfactory.log';

    /** @var int[] */
    protected $levelMapping = array(
        LogLevel::EMERGENCY => Zend_Log::EMERG,
        LogLevel::ALERT => Zend_Log::ALERT,
        LogLevel::CRITICAL => Zend_Log::CRIT,
        LogLevel::ERROR => Zend_Log::ERR,
        LogLevel::WARNING => Zend_Log::WARN,
        LogLevel::NOTICE => Zend_Log::NOTICE,
        LogLevel::INFO => Zend_Log::INFO,
        LogLevel::DEBUG => Zend_Log::DEBUG,
    );

    /**
     * @var Postdirekt_Addressfactory_Model_Config
     */
    protected $config;

    /**
     * @var string Store Code
     */
    protected $scope;

    public function __construct(array $args)
    {
        $this->config = Mage::getSingleton('postdirekt_addressfactory/config');
        $this->scope = $args[self::PROPERTY_SCOPE] ?? Mage::app()->getStore()->getCode();
    }

    public function log($level, $message, array $context = array())
    {
        if (!$this->config->isLoggingEnabled($this->_scope)) {
            return;
        }

        $zendLogLevel = $this->levelMapping[$level];
        $configLogLevel = $this->config->getLogLevel($this->_scope);

        // only log if configured loglevel is below or same as the logLevel that's coming in via parameter
        if ($zendLogLevel <= $configLogLevel) {
            Mage::log($message, $zendLogLevel, self::LOG_FILE);
        }
    }
}
