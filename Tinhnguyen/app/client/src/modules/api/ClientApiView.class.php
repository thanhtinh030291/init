<?php

namespace Lza\App\Client\Modules\Api;


use Lza\LazyAdmin\Runtime\BaseApi;

use Monolog\Logger;
use Monolog\Handler\LogglyHandler;

/**
 * Base View for API
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class ClientApiView extends BaseApi
{
    const HEADER_SESSION_ID = 'Session-ID';
    const HEADER_LANGUAGE = 'Language';
    const HEADER_OTP = 'OTP';

    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreate()
    {
        parent::onCreate();
        
        $this->module = $this->env->child1;
        $this->action = $this->env->child2;
        if ($this->action === '')
        {
            $this->action = 'get';
        }
        $this->middlewares = [
            'all' => [
                [$this, 'monolog']
            ]
        ];
    }

    public function monolog( $request, $response, $next){
        $log = new Logger(LOGGER_APP_NAME);
        $log->pushHandler(new LogglyHandler(LOGGER_TOKEN, Logger::INFO));
        $response = $next($request, $response);

        $log->info("API: ".$request->getURI(),['request' => json_decode($request->getBody()), 'response' => json_decode($response->getBody())]);

        return $response;
    }

    /**
     * Is this page requires login?
     *
     * @throws
     */
    protected function isLoginRequired()
    {
        return true;
    }
}
