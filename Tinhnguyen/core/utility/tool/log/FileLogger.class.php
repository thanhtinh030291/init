<?php

namespace Lza\LazyAdmin\Utility\Tool\Log;


/**
 * File Logger helps write logs to files
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class FileLogger extends Logger
{
    /**
     * @throws
     */
    protected function writeMessage($severity, $message)
    {
        global $ds;

        $label = LogLevel::getLabel($severity);
        $date = date('Ymd');
        $folder = DOCUMENT_ROOT . 'temp/log';
        $file = RUNNING_MODE . '-' . strtolower($label) . "-{$date}.log";
        $this->createFileIfNotExists($folder, $file);

        error_reporting(E_ALL);
        ini_set("log_errors", 1);
        ini_set("error_log" , "{$folder}{$ds}{$file}");
        error_log("[{$label}]: {$message}");
    }

    /**
     * @throws
     */
    private function createFileIfNotExists($folder, $file)
    {
        global $ds;
        if (!file_exists($folder))
        {
            mkdir($folder, 0775, true);
        }
        if (!file_exists("{$folder}{$ds}{$file}"))
        {
            file_put_contents("{$folder}{$ds}{$file}", '');
        }
    }
}
