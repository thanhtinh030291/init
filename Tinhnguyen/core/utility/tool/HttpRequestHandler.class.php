<?php

namespace Lza\LazyAdmin\Utility\Tool;


use Box\Spout\Common\Singleton;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseTask;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Http Request Handler helps send requests
 *
 * @var mailer
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class HttpRequestHandler implements BaseTask
{
    use Singleton;

    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const METHOD_PUT = 'put';
    const METHOD_PATCH = 'patch';
    const METHOD_DELETE = 'delete';
    const METHOD_OPTIONS = 'options';

    private $verify;
    private $subject;

    /**
     * @throws
     */
    private function __construct($verify = false)
    {
        $this->verify = $verify;
        $this->subject = "[" . WEBSITE_ROOT . "][ERROR]: HTTP Request Error";
        $this->subject2 = "[" . WEBSITE_ROOT . "][ERROR]: Error after sending Http Request";
    }

    /**
     * @throws
     */
    public function request($request, $echo = false, $direct = true)
    {
        $callback = $request['callback'] ?? null;

        if ($direct)
        {
            $headers = $request['headers'] ?? null;
            $data = $request['data'] ?? null;
            $extra = $request['extra'] ?? null;
        }
        else
        {
            $headers = json_decode($request['headers'], true);
            $data = json_decode($request['data'], true);
            $extra = json_decode($request['extra'], true);
        }

        $response = $this->sendRquest(
            $request['base_url'],
            $request['url'],
            $request['method'],
            $headers,
            $data
        );

        if ($response === false)
        {
            $this->sendAgainLater($request, $echo, $direct);
            return false;
        }

        $status = (int) $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status >= 500)
        {
            $message = "Dear " . SUPPORT_EMAIL . ",\n\nPlease check:\n{$body}\n\nThank you!";
            $this->mailer->add(
                'system',
                $this->setting->companyName,
                $this->setting->email,
                SUPPORT_EMAIL,
                SUPPORT_EMAIL,
                $this->subject,
                $message
            );

            $this->sendAgainLater($request, $echo, $direct);
            return false;
        }
        elseif ($status >= 400 && $status < 500)
        {
            $message = "Dear " . SUPPORT_EMAIL . ",\n\nPlease check:\n\n{$body}\n\nThank you!";
            $this->mailer->add(
                'system',
                $this->setting->companyName,
                $this->setting->email,
                SUPPORT_EMAIL,
                SUPPORT_EMAIL,
                $this->subject,
                $message
            );

            $this->finish($request, $echo, $direct);
            return false;
        }

        if (!empty($callback))
        {
            list($class, $method) = explode('::', $callback);
            $vars = DIContainer::getMethodVars($class, $method);
            if (!call_user_func_array($callback, array_merge($vars, [$status, $body, $extra])))
            {
                $message = "
                    Function: {$callback}\n\n
                    Http Status: {$status}\n\n
                    Response: {$body}\n\n
                    Extra: {$request['extra']}
                ";
                $this->mailer->add(
                    'system',
                    $this->setting->companyName,
                    $this->setting->email,
                    SUPPORT_EMAIL,
                    SUPPORT_EMAIL,
                    $this->subject2,
                    $message
                );
                $this->suspend($request, $echo, $direct);
                return false;
            }
        }

        $this->finish($request, $echo, $direct);
        return true;
    }

    /**
     * @throws
     */
    public function add(
        $user, $baseUrl, $url, $method, $headers = null,
        $data = null, $callback = '', $extra = null, $nextTry = null
    )
    {
        if (!empty($headers) && !is_string($headers))
        {
            $headers = json_encode($headers);
        }

        if (!empty($data) && !is_string($data))
        {
            $data = json_encode($data);
        }

        if (!empty($extra) && !is_string($extra))
        {
            $extra = json_encode($extra);
        }

        $model = ModelPool::getModel('lzahttprequest');
        return $model->create($user, [
            'base_url' => $baseUrl,
            'url' => $url,
            'method' => $method,
            'headers' => $headers,
            'data' => $data,
            'extra' => $extra,
            'callback' => $callback,
            'next_try' => $nextTry ?? date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @throws
     */
    public function execute($echo = false)
    {
        $requests = $this->get();
        foreach ($requests as $request)
        {
            if ($request['next_try'] === null || strtotime('now') < strtotime($request['next_try']))
            {
                continue;
            }

            if ($echo)
            {
                println("{$request['method']}: {$request['url']}");
                println("Data: {$request['data']}");
            }

            $this->request($request, $echo, false);
        }
    }

    /**
     * @throws
     */
    private function get()
    {
        $model = ModelPool::getModel('lzahttprequest');
        $requests = REQUEST_SEND_LIMIT > 0
            ? $model->limit(REQUEST_SEND_LIMIT)
            : $model->where('1=1');
        return $requests !== false ? $requests : [];
    }

    /**
     * @throws
     */
    public function sendRquest($baseUri, $url, $method, $headers = null, $json = null)
    {
        try
        {
            $client = new Client([
                'base_uri' => $baseUri,
                'verify' => $this->verify
            ]);
            $request = [];
            if ($headers !== null)
            {
                $request['headers'] = $headers;
            }
            if ($json !== null)
            {
                $request['json'] = $json;
            }
            return $client->$method($url, $request);
        }
        catch (RequestException $e)
        {
            return false;
        }
    }

    /**
     * @throws
     */
    private function finish($request, $echo, $direct)
    {
        if (!$direct)
        {
            $request->delete();
        }

        if ($echo)
        {
            println("Successful!\n");
        }
    }

    /**
     * @throws
     */
    private function sendAgainLater($request, $echo, $direct)
    {
        if ($direct)
        {
            $this->add(
                $request['user'],
                $request['base_url'],
                $request['url'],
                $request['method'],
                $request['headers'] ?? null,
                $request['data'] ?? null,
                $request['callback'] ?? null,
                $request['extra'] ?? null
            );
        }
        else
        {
            $request->update([
                'upd_by' => 'system',
                'next_try' => date('Y-m-d H:i:s', strtotime(HTTP_REQUEST_TRY_INTERVAL))
            ]);
        }

        if ($echo)
        {
            println("Failed!\n");
        }
    }

    /**
     * @throws
     */
    private function suspend($request, $echo, $direct)
    {
        if (!$direct)
        {
            $request->update([
                'upd_by' => 'system',
                'next_try' => null
            ]);
        }
        if ($echo)
        {
            println("Failed!\n");
        }
    }
}
