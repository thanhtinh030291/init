<?php

namespace Lza\LazyAdmin\Utility\Tool\Log;


/**
 * Email Logger helps send logs through emails
 *
 * @var mailer
 * @var setting
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class EmailLogger extends Logger
{
    /**
     * @throws
     */
    protected function writeMessage($severity, $message)
    {
        $email = SUPPORT_EMAIL;
        $site = WEBSITE_ROOT;
        $time = date('Y-m-d H:i:s');

        $label = LogLevel::getLabel($severity);

        $subject = "[{$site}][{$label}][{$time}]: Fatal Error";
        $message = "Dear {$email},\n\nPlease check:\n{$message}\n\nThank you!";

        $this->mailer->add(
            'system',
            $this->setting->companyName,
            $this->setting->email,
            $email,
            $email,
            $subject,
            $message
        );
    }
}
