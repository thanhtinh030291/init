<?php

namespace Lza\LazyAdmin\Utility\Tool;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseTask;
use Lza\LazyAdmin\Utility\Pattern\Singleton;
use PHPMailer;

/**
 * Mailer helps send emails
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PHPMailHandler implements MailHandler, BaseTask
{
    use Singleton;

    /**
     * @throws
     */
    private function __construct()
    {
        $this->mailer = new PHPMailer();
        $model = ModelPool::getModel('Lzasetting');

        $settings = $model->where('lzasection.id', 'setting_smtp');
        foreach ($settings as $setting)
        {
            if ($setting['id'] === 'smtp_host')
            {
                $host = $setting['value'];
            }
            if ($setting['id'] === 'smtp_port')
            {
                $port = $setting['value'];
            }
            if ($setting['id'] === 'smtp_auth')
            {
                $auth = $setting['value'];
            }
            if ($setting['id'] === 'smtp_secure')
            {
                $secure = $setting['value'];
            }
            if ($setting['id'] === 'smtp_username')
            {
                $username = $setting['value'];
            }
            if ($setting['id'] === 'smtp_password')
            {
                $password = $setting['value'];
            }
            if ($setting['id'] === 'is_smtp')
            {
                $smtp = $setting['value'];
            }
        }

        if ($smtp === 'Yes')
        {
            $this->mailer->isSMTP();
        }

        $this->mailer->Host = gethostbyname($host);
        $this->mailer->SMTPDebug = DEBUG_SMTP;
        $this->mailer->Port = intval($port);
        $this->mailer->SMTPAuth = $auth === 'Yes' ? true : false;
        if ($secure !== "None")
        {
            $this->mailer->SMTPSecure = strtolower($secure);
        }
        $this->mailer->Username = $username;
        $this->mailer->Password = $password;

        $this->mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
    }

    /**
     * @throws
     */
    public function add($user, $fromName, $fromEmail, $toName, $toEmail, $subject, $message)
    {
        if (RUNNING_MODE !== 'production')
        {
            if (strpos($subject, 'Fatal Error') !== false)
            {
                $toName = SUPPORT_EMAIL;
                $toEmail = SUPPORT_EMAIL;
            }
            else
            {
                $toName = SUPPORT_EMAIL_2;
                $toEmail = SUPPORT_EMAIL_2;
            }
        }
        $model = ModelPool::getModel('lzaemail');
        return $model->create($user, [
            'from_name' => $fromName,
            'from_email' => $fromEmail,
            'to_name' => $toName,
            'to_email' => $toEmail,
            'subject' => $subject,
            'message' => $message,
            'try' => EMAIL_SEND_TRY
        ]);
    }

    /**
     * @throws
     */
    public function execute($echo = false)
    {
        $emails = $this->get();
        foreach ($emails as $email)
        {
            $sender = [
                'name' => $email['from_name'],
                'email' => $email['from_email']
            ];
            $receivers = [[
                'name' => $email['to_name'],
                'email' => $email['to_email']
            ]];

            if ($echo)
            {
                println("Send email to {$email['to_email']}");
                println("Subject {$email['subject']}");
            }
            $sent = $this->sendEmail(
                $sender, $receivers,
                $email['subject'],
                $email['message'],
                true
            );
            if ($sent || $email['try'] === 0)
            {
                $email->delete();
                if ($echo)
                {
                    println("Successful!\n");
                }
            }
            else
            {
                $email->update([
                    'upd_by' => 'system',
                    'try' => $email['try'] - 1
                ]);
                if ($echo)
                {
                    println("Failed!\n");
                }
            }
        }
    }

    /**
     * @throws
     */
    public function get()
    {
        $model = ModelPool::getModel('lzaemail');
        $emails = EMAIL_SEND_LIMIT > 0
            ? $model->limit(EMAIL_SEND_LIMIT)
            : $model->where('1=1');
        return $emails !== false ? $emails : [];
    }

    /**
     * @throws
     */
    public function sendEmail($sender, $receivers, $subject, $message, $isHtml = false)
    {
        if (!SEND_EMAIL)
        {
            return true;
        }

        $this->mailer->setFrom(
            $sender['email'], $sender['name']
        );
        $this->mailer->addReplyTo(
            $sender['email'], $sender['name']
        );

        foreach ($receivers as $receiver)
        {
            $this->mailer->addAddress(
                $receiver['email'], $receiver['name']
            );
        }

        $this->mailer->Subject = $subject;
        $this->mailer->CharSet = "utf-8";
        $this->mailer->IsHTML($isHtml);

        $this->mailer->Body = $message;

        return $this->mailer->send();
    }
}
