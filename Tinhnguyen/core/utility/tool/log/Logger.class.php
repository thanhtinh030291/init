<?php

namespace Lza\LazyAdmin\Utility\Tool\Log;


use Lza\LazyAdmin\Utility\Pattern\ChainOfResponsibility;

/**
 * Logger helps write and show logs
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class Logger extends ChainOfResponsibility
{
    /**
     * @var LogLevel
     */
    protected $logLevel;

    /**
     * @throws
     */
    public function __construct($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @throws
     */
    public function log($severity, $message)
    {
        if ($this->logLevel <= $severity)
        {
            $this->writeMessage($severity, $message);
        }

        if (isset($this->nextResponsibility))
        {
            $this->nextResponsibility->log($severity, $message);
        }
    }

    protected abstract function writeMessage($severity, $message);
}
