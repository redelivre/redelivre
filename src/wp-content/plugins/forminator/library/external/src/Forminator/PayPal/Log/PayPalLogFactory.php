<?php

namespace Forminator\PayPal\Log;

use Forminator\Psr\Log\LoggerInterface;

interface PayPalLogFactory
{
    /**
     * Returns logger instance implementing LoggerInterface.
     *
     * @param string $className
     * @return LoggerInterface instance of logger object implementing LoggerInterface
     */
    public function getLogger($className);
}
