<?php

namespace Lza\LazyAdmin\Utility\Tool\Log;


/**
 * Log Level Enumeration
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LogLevel
{
    const DEBUG = 1;
    const INFO = 2;
    const SUCCESS = 3;
    const WARNING = 4;
    const ERROR = 5;
    const FATAL = 6;

    public static function getLabel($level)
    {
        switch ($level)
        {
            case self::DEBUG:
                return 'DEBUG';
            case self::INFO:
                return 'INFO';
            case self::SUCCESS:
                return 'SUCCESS';
            case self::WARNING:
                return 'WARNING';
            case self::ERROR:
                return 'ERROR';
            case self::FATAL:
                return 'FATAL';
            default:
                return 'NONE';
        }
    }
}
