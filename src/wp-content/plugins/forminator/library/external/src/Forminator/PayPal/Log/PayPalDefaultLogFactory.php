<?php

namespace Forminator\PayPal\Log;

use Forminator\Psr\Log\LoggerInterface;

/**
 * Class PayPalDefaultLogFactory
 *
 * This factory is the default implementation of Log factory.
 *
 * @package Forminator\PayPal\Log
 */
class PayPalDefaultLogFactory implements PayPalLogFactory
{
    /**
     * Returns logger instance implementing LoggerInterface.
     *
     * @param string $className
     * @return LoggerInterface instance of logger object implementing LoggerInterface
     */
    public function getLogger($className)
    {
        return new PayPalLogger($className);
    }
}
