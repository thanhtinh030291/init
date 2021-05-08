<?php

namespace Lza\LazyAdmin\Utility\Tool;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseTask;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * SmsHandler helps send SMSes
 *
 * @var setting
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class FptSmsHandler implements SmsHandler, BaseTask
{
    use Singleton;

    const GRANT_TYPE = 'client_credentials';
    const SCOPE = 'send_brandname_otp';
    const URL_GET_TOKEN = SMS_API . 'oauth2/token';
    const URL_SEND = SMS_API . 'api/push-brandname-otp';
    const EXPIRE = 3500;

    private $client;

    /**
     * @throws
     */
    private function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * @throws
     */
    public function add($user, $receiver, $message)
    {
        if (RUNNING_MODE !== 'production')
        {
            $receiver = SMS_FALLBACK;
        }
        $model = ModelPool::getModel('lzasms');
        return $model->create($user, [
            'receiver' => $receiver,
            'message' => $message,
            'try' => SMS_SEND_TRY
        ]);
    }

    /**
     * @throws
     */
    public function execute($echo = false)
    {
        $smses = $this->get();
        foreach ($smses as $sms)
        {
            if ($echo)
            {
                println("Send SMS to {$sms['receiver']}");
                println("Message {$sms['message']}");
            }

            $sent = $this->sendSms(
                $sms['receiver'],
                $sms['message']
            );

            if ($sent || $sms['try'] === 0)
            {
                $sms->delete();
                if ($echo)
                {
                    println("Successful!\n");
                }
            }
            else
            {
                $sms->update([
                    'upd_by' => 'system',
                    'try' => $sms['try'] - 1
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
        $model = ModelPool::getModel('lzasms');
        $smses = SMS_SEND_LIMIT > 0
            ? $model->limit(SMS_SEND_LIMIT)
            : $model->where('1=1');
        return $smses !== false ? $smses : [];
    }

    /**
     * @throws
     */
    public function sendSms($receiver, $message)
    {
        if (!SEND_SMS)
        {
            return true;
        }

        $token = $this->getToken();
        $receiver = preg_replace('/[^0-9]+/', '', $receiver);
        try
        {
            $response = $this->client->request("POST", self::URL_SEND , [
                'form_params' => [
                    'access_token' => $token,
                    'session_id' => VERSION_ID,
                    'scope' => self::SCOPE,
                    'BrandName' => 'PACIFICROSS',
                    'Phone' => $receiver,
                    'Message' => base64_encode($message)
                ]
            ]);
            $response = json_decode($response->getBody()->getContents(), true);
            return $response;
        }
        catch (ClientException $e)
        {
            $response = $e->getResponse()->getBody(true);
            $response = json_decode((string) $response, true);
            return $response;
        }
    }

    /**
     * @throws
     */
    private function getToken()
    {
        $smsToken = $this->setting->smsToken;
        $smsTokenTime = strtotime($this->setting->smsTokenTime);
        $now = strtotime('now');
        $diff = $now - $smsTokenTime;

        if ($smsToken == null || $smsTokenTime == null || $diff >= self::EXPIRE)
        {
            $response = $this->client->request("POST", self::URL_GET_TOKEN, [
                'form_params' => [
                    'client_id' => SMS_CLIENT_ID,
                    'client_secret' => SMS_SECRET,
                    'grant_type' => self::GRANT_TYPE,
                    'scope' => self::SCOPE,
                    'session_id' => VERSION_ID
                ]
            ]);
            $response =  json_decode($response->getBody()->getContents(), true);
            $this->setting->smsToken = $response['access_token'];
            $this->setting->smsTokenTime = date('Y-m-d H:i:s', $now);
            return $response['access_token'];
        }
        return $smsToken;
    }
}
