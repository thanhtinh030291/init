<?php

namespace Lza\LazyAdmin\Utility\Tool\Log;


use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * AppLogger Singleton
 * Chain of Logger Responsibility
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class AppLogger
{
    use Singleton;

    /**
     * @var Logger head of the Logger Responsibilities Chain
     */
    private $logger;

    /**
     * @throws
     */
    private function __construct()
    {
        $emailLogger = DIContainer::resolve(EmailLogger::class, LogLevel::FATAL);

        $fileLogger = DIContainer::resolve(FileLogger::class, LogLevel::ERROR);
        $fileLogger->setNext($emailLogger);

        $level = DEBUG ? LogLevel::INFO : LogLevel::INFO;
        $sessionLogger = DIContainer::resolve(SessionLogger::class, $level);
        $sessionLogger->setNext($fileLogger);

        $this->logger = $sessionLogger;
    }

    /**
     * @throws
     */
    public function log($severity, $message)
    {
        $this->logger->log($severity, $message);
    }
}
