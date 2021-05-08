<?php

namespace Lza\LazyAdmin;


use Exception;
use Lza\LazyAdmin\Utility\Data\SessionHandler;
use Lza\LazyAdmin\Utility\Data\Setting;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Text\Encryptor;
use Lza\LazyAdmin\Utility\Text\Environment;
use Lza\LazyAdmin\Utility\Text\Inflector;
use Lza\LazyAdmin\Utility\Text\Internationalization;
use Lza\LazyAdmin\Utility\Text\Query;
use Lza\LazyAdmin\Utility\Text\Request;
use Lza\LazyAdmin\Utility\Text\SecurityToken;
use Lza\LazyAdmin\Utility\Text\Sql;
use Lza\LazyAdmin\Utility\Text\Validator;
use Lza\LazyAdmin\Utility\Text\Helper;
use Lza\LazyAdmin\Utility\Tool\Log\AppLogger;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;
use Lza\LazyAdmin\Utility\Tool\PHPMailHandler;
use Lza\LazyAdmin\Utility\Tool\SmartyHandler;

/**
 * Core is the core class of Lza
 * User must implement a class extend this class and call function main on the index.php
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class Core
{
    const ERROR_DETAILS = "
        <!DOCTYPE html><html>
            <body style='background-color: #5277B2; color: #FFFFFF'>
                <div style='position:fixed; top: 15%%; left: 25%%; width: 55%%'>
                    <span style='font-size: 24'>
                        <strong style='font-size: 192px'>:(</strong> <br>
                        The website ran into a problem and need to be fixed!
                        Please contact <a href='mailto:%s' style='color: #ffffff'>%s</a> for support.
                        <pre>%s</pre>
                    </span>
                </div>
            </body>
        </html>
    ";

    protected $parameter;
    protected $logger;
    protected $env;
    protected $setting;
    protected $request;
    protected $router;

    private static $instance;

    /**
     * @throws
     */
    public static function main()
    {
        self::$instance = self::$instance ?: new static();
    }

    /**
     * @throws
     */
    protected function __construct()
    {
        $uriParts = explode('?', str_replace(['//', ROOT_FOLDER], '/', $_SERVER['REQUEST_URI']));
        $params = explode('/', trim($uriParts[0], '/'));
        $region = 'Client';
        $region = $params[0] === 'lzaadmin' ? 'Admin' : $region;
        $region = $params[0] === 'restful' ? 'Restful' : $region;

        $this->setting = DIContainer::bindObject('setting', Setting::class);
        DIContainer::bindSingleton('session', SessionHandler::class);
        DIContainer::bindSingleton('mailer', PHPMailHandler::class);
        $this->logger = DIContainer::bindObject('logger', AppLogger::class);

        try
        {
            $this->preventXss();

            $this->env = DIContainer::bindObject('env', Environment::class);
            $this->request = DIContainer::bindObject('request', Request::class, $_REQUEST);
            $this->request->get = DIContainer::resolve(Request::class, $_GET);
            $this->request->post = DIContainer::resolve(Request::class, $_POST);

            DIContainer::bindSingleton('inflector', Inflector::class);
            DIContainer::bindSingleton('encryptor', Encryptor::class);
            DIContainer::bindSingleton('csrf', SecurityToken::class);

            $this->router = DIContainer::resolve("Lza\\App\\{$region}\\{$region}Router");

            $this->defineConstants($region);
            $this->initEnvironment($region, count($uriParts) > 1 ? $uriParts[1] : null);

            DIContainer::bindSingleton('i18n', Internationalization::class, RES_PATH . "/configs/");
            DIContainer::bindSingleton('query', Query::class, RES_PATH . "/sqls/");
            DIContainer::bindSingleton('sql', Sql::class, RES_PATH . "/sqls/");
            DIContainer::bindSingleton('validator', Validator::class);
            DIContainer::bindSingleton('str_helper', Helper::class);
            DIContainer::bindSingleton('layoutHandler', SmartyHandler::class,
                DOCUMENT_ROOT . "app/" . chain_case($region) . "/res/layouts/",
                COMPILE_PATH . chain_case($region)
            );

            $this->route($region, $params);
            $this->launch($region);
        }
        catch (Exception $e)
        {
            $message = $e->getMessage();
            $stackTrace = nl2br($e->getTraceAsString());
            $error = "{$message}\n\n{$stackTrace}";

            $this->logger->log(LogLevel::FATAL, $error);
            $this->dd(DEBUG_ERROR ? $error : null);
        }
    }

    /**
     * @throws
     */
    private function preventXss()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, '<') !== false || strpos($uri, '/>') !== false)
        {
            $this->dd();
        }

        foreach ($_COOKIE as $key => $value)
        {
            if (strpos($value, '<') !== false || strpos($value, '/>') !== false)
            {
                $this->dd();
            }
        }
    }

    /**
     * @throws
     */
    private function dd($error = null)
    {
        if (php_sapi_name() !== 'cli')
        {
            die(sprintf(self::ERROR_DETAILS, SUPPORT_EMAIL, SUPPORT_EMAIL, $error));
        }
        else
        {
            die($error);
        }
    }

    /**
     * @throws
     */
    protected function defineConstants($region)
    {

    }

    /**
     * @throws
     */
    protected function initEnvironment($region, $requests)
    {
        if ($requests !== null)
        {
            $gets = explode('&', $requests);
            foreach ($gets as $get)
            {
                if (strpos($get, '=') === false)
                {
                    $get .= '=true';
                }
                list($key, $value) = explode('=', $get);
                $this->request->$key = $value;
            }
        }

        date_default_timezone_set($this->setting->timezone);
    }

    protected abstract function route($region, $params);

    protected abstract function launch($region);
}
