<?php

namespace Lza\LazyAdmin\Utility\Tool;


use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Lza\Config\Models\ModelPool;

/**
 * Slim Handler handle Slim API Dispatcher
 *
 * @var encryptor
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SlimHandler implements ApiHandler
{
    private $app;
    private $route;
    private $method;
    private $allow = false;

    /**
     * @throws
     */
    public function __construct($container = null)
    {
        if ($container === null)
        {
            $container = [
                'settings' => []
            ];
        }
        if (DEBUG_ERROR)
        {
            $container['settings']['displayErrorDetails'] = true;
        }

        $this->route = strtolower($_SERVER['REQUEST_URI']);
        $this->route = explode('?', $this->route);
        $this->route = $this->route[0];

        $this->app = new App($container);
        $this->app->add([$this, 'authenticate']);
    }

    /**
     * Authenticate API
     *
     * @throws
     */
    public function authenticate(Request $request, Response $response, callable $next)
    {
        $this->method = strtolower($request->getMethod());

        $token = $request->getHeaderLine(self::HEADER_AUTHORIZATION);
        if (empty($token))
        {
            return $response->withStatus(self::HTTP_STATUS_FORBIDDEN, 'Access denied');
        }
        $token = $this->encryptor->hash($token, 2);

        $model = ModelPool::getModel('Lzaapi');
        $tokens = $model->where("password = ?", $token);
        $token = $tokens->fetch();
        if ($token === false)
        {
            return $response->withStatus(self::HTTP_STATUS_FORBIDDEN, 'Access denied');
        }

        $permissions = json_decode($token['permissions'], true);
        if ($permissions === null || count($permissions) === 0)
        {
            return $next($request, $response);
        }

        $this->checkPermission($permissions);
        if ($this->allow)
        {
            return $next($request, $response);
        }
        return $response->withStatus(self::HTTP_STATUS_FORBIDDEN, 'Access denied');
    }

    /**
     * Check if user is allowed to call API
     *
     * @throws
     */
    public function checkPermission($permissions, $route = '')
    {
        if ($this->allow)
        {
            return;
        }
        foreach ($permissions as $key => $value)
        {
            if (is_array($value))
            {
                $this->checkPermission($value, "{$route}/{$key}");
            }
            elseif (text_start_with($this->route, $route) && $key === $this->method)
            {
                return $this->allow = true;
            }
        }
    }

    /**
     * @throws
     */
    public function __call($method, $params)
    {
        return call_user_func_array([$this->app, $method], $params);
    }

    /**
     * Call GET method
     *
     * @throws
     */
    public function get($uri, $callback)
    {
        return $this->app->get($uri, $callback);
    }

    /**
     * Call POST method
     *
     * @throws
     */
    public function post($uri, $callback)
    {
        return $this->app->post($uri, $callback);
    }

    /**
     * Call PUT method
     *
     * @throws
     */
    public function put($uri, $callback)
    {
        return $this->app->put($uri, $callback);
    }

    /**
     * Call PATCH method
     *
     * @throws
     */
    public function patch($uri, $callback)
    {
        return $this->app->patch($uri, $callback);
    }

    /**
     * Call DELETE method
     *
     * @throws
     */
    public function delete($uri, $callback)
    {
        return $this->app->delete($uri, $callback);
    }

    /**
     * Call OPTIONS method
     *
     * @throws
     */
    public function options($uri, $callback)
    {
        return $this->app->options($uri, $callback);
    }

    /**
     * Run API
     *
     * @throws
     */
    public function run()
    {
        $this->app->run();
    }
}