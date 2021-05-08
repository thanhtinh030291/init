<?php

namespace Lza\LazyAdmin\Utility\Tool;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseTask;
use Lza\LazyAdmin\Utility\Pattern\Singleton;
use paragraph1\phpFCM\Client;
use paragraph1\phpFCM\Message;
use paragraph1\phpFCM\Notification;
use paragraph1\phpFCM\Recipient\Device;
use paragraph1\phpFCM\Recipient\Topic;
use paragraph1\phpFCM\Recipient\GroupTopic;

/**
 * FCM Handler helps send Push Notification
 *
 * @var encryptor
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class FirebaseCloudMessageHandler implements NotificationHandler, BaseTask
{
    use Singleton;

    private $client;

    /**
     * @throws
     */
    private function __construct()
    {
        $this->client = new Client();
        $this->client->setApiKey(PUSH_NOTIFICATION_API_KEY);
    }

    /**
     * @throws
     */
    public function add(
        $user, $type, $receivers, $subject, $message,
        $data = [], $icon = null, $color = null, $badge = false
    )
    {
        $receivers = $this->encryptor->jsonEncode($receivers);
        $data = $this->encryptor->jsonEncode($data);
        $model = ModelPool::getModel('Lzanotification');
        return $model->create($user, [
            'type' => $type,
            'receivers' => $receivers,
            'subject' => $subject,
            'message' => $message,
            'data' => $data,
            'icon' => $icon,
            'color' => $color,
            'badge' => $badge,
            'try' => EMAIL_SEND_TRY
        ]);
    }

    /**
     * @throws
     */
    public function execute($echo = false)
    {
        $methods = [
            'device' => 'sendToDevices',
            'topic' => 'sendToTopics',
            'group' => 'sendToGroupTopics'
        ];
        $notes = $this->get();
        foreach ($notes as $note)
        {
            $method = $methods[$note['type']];
            $receivers = $this->encryptor->jsonDecode($note['receivers'], true);

            if ($echo)
            {
                println("Push notification to {$note['receivers']}");
                println("Subject {$note['subject']}");
            }
            $sent = $this->$method(
                $receivers,
                $note['subject'],
                $note['message'],
                $this->encryptor->jsonDecode($note['data'], true),
                $note['icon'],
                $note['color'],
                $note['badge']
            );
            if ($sent || $note['try'] === 0)
            {
                $note->delete();
                if ($echo)
                {
                    println("Successful!\n");
                }
            }
            else
            {
                $note->update([
                    'upd_by' => 'system',
                    'try' => $note['try'] - 1
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
        $model = ModelPool::getModel('lzanotification');
        $notes = $model->where('1=1');
        return $notes !== false ? $notes : [];
    }

    /**
     * @throws
     */
    public function sendToDevices(
        $devices, $subject, $message, $data = [], $icon = null, $color = null, $badge = false, $echo = false
    )
    {
        if (!PUSH_NOTIFICATION)
        {
            return;
        }

        $note = $this->createNote($subject, $message, $icon, $color, $badge);

        $message = new Message();
        $message->setNotification($note);
        foreach ($devices as $device)
        {
            if ($echo)
            {
                println("Send notification to {$device}");
            }
            $message->addRecipient(new Device($device));
        }
        $message->setData($data);

        if ($echo)
        {
            println("Message {$note}");
        }
        $response = $this->client->send($message);
        $code = $response->getStatusCode();
        $result = $code === 200;

        println($result ? 'OK!' : 'Failed!');
        return $result;
    }

    /**
     * @throws
     */
    public function sendToTopic(
        $topic, $subject, $message, $data = [], $icon = null, $color = null, $badge = false, $echo = false
    )
    {
        if (!PUSH_NOTIFICATION)
        {
            return;
        }

        $note = $this->createNote($subject, $message, $icon, $color, $badge);

        $message = new Message();
        $message->addRecipient(new Topic($topic));
        $message->setNotification($note);
        $message->setData($data);

        if ($echo)
        {
            println("Send notification to {$topic}");
            println("Message {$note}");
        }
        $response = $this->client->send($message);
        $code = $response->getStatusCode();
        $result = $code === 200;

        println($result ? 'OK!' : 'Failed!');
        return $result;
    }

    /**
     * @throws
     */
    public function sendToGroupTopic(
        $topics, $subject, $message, $data = [], $icon = null, $color = null, $badge = false, $echo = false
    )
    {
        if (!PUSH_NOTIFICATION)
        {
            return;
        }

        $note = $this->createNote($subject, $message, $icon, $color, $badge);

        $message = new Message();
        $message->addRecipient(new GroupTopic($topics));
        $message->setNotification($note);
        $message->setData($data);

        if ($echo)
        {
            println("Send notification to {$topics}");
            println("Message {$note}");
        }
        $response = $this->client->send($message);
        $code = $response->getStatusCode();
        $result = $code === 200;

        println($result ? 'OK!' : 'Failed!');
        return $result;
    }

    /**
     * @throws
     */
    private function createNote($subject, $message, $icon = null, $color = null, $badge = false)
    {
        $note = new Notification($subject, $message);
        $note->setBadge($badge);
        if (!empty($icon))
        {
            $note->setIcon($icon);
        }
        if (!empty($color))
        {
            $note->setColor($color);
        }
        return $note;
    }
}
