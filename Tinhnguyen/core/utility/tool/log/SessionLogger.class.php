<?php

namespace Lza\LazyAdmin\Utility\Tool\Log;


use Lza\LazyAdmin\Utility\Data\SessionHandler;

/**
 * Session Logger helps write logs to session
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SessionLogger extends Logger
{
    /**
     * @var SessionHandler
     */
    protected $session;

    /**
     * @throws
     */
    public function __construct($logLevel)
    {
        parent::__construct($logLevel);
        $this->session = SessionHandler::getInstance();
        $this->session->start();
    }

    /**
     * @throws
     */
    protected function writeMessage($severity, $message)
    {
        $label = strtolower(LogLevel::getLabel($severity));
        $this->session->add("alert_{$label}", $message);
    }
}
