<?php

namespace Lza\LazyAdmin\Utility\Tool\Log;


use Lza\Config\Models\ModelPool;

/**
 * Database Logger helps write logs to database
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DatabaseLogger extends Logger
{
    /**
     * @throws
     */
    protected function writeMessage($severity, $message)
    {
        $label = LogLevel::getLabel($severity);
        ModelPool::getModel('lzalogger')->insert([
            'severity' => $label,
            'message' => $message
        ]);
    }
}
