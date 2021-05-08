<?php

namespace Lza\LazyAdmin\Runtime;


use Exception;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Base API
 * Abstract Class for any API View in the Application
 * A special view which customized for API
 *
 * @var apiHandler
 * @var csrf
 * @var datetime
 * @var env
 * @var i18n
 * @var logger
 * @var presenter
 * @var region
 * @var request
 * @var session
 * @var setting
 * @var validator
 * @var view
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class BaseApi
{
    /**
     * @var array Headers
     */
    protected $headers;

    /**
     * @var object Data to be used in the layouts
     */
    protected $data;

    /**
     * @throws
     */
    public function show()
    {
        $this->onCreate();
        $this->onEventHandle();
    }

    /**
     * @throws
     */
    protected function onCreate()
    {
        $this->presenter->setViewer($this);
        $this->data = $this->presenter->getData();
        $this->headers = getallheaders();
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->middlewares = [];
    }

    /**
     * @throws
     */
    protected function onEventHandle()
    {
        $uri = explode('?', trim($_SERVER['REQUEST_URI'], '/'));

        $path = "api/";

        $params = explode($path, $uri[0]);
        $params = trim($params[count($params) - 1], '/');
        if ($params !== '')
        {
            $params = explode('/', $params);
            $path .= "{$params[0]}/{$params[1]}/{$params[2]}";
            $this->action = $params[2];
            array_splice($params, 0, 3);
            foreach ($params as $no => $param)
            {
                $path .= '/{param_' . $no . '}';
            }
        }

        $route1 = $this->apiHandler->{$this->method}('/'. $path, [$this, 'route']);
        $route2 = $this->apiHandler->{$this->method}('/'. $path . '/', [$this, 'route']);
        if( !empty($this->middlewares[$this->action]) ) {
            foreach($this->middlewares[$this->action] as $middleware) {
                $route1 = $route1->add($middleware);
                $route2 = $route2->add($middleware);
            }
        }
        if( !empty($this->middlewares['all']) ) {
            foreach($this->middlewares['all'] as $middleware) {
                $route1 = $route1->add($middleware);
                $route2 = $route2->add($middleware);
            }
        }

        $this->apiHandler->run();
    }

    /**
     * @throws
     */
    public function route(Request $request, Response $response, array $args)
    {
        $contentType = isset($this->headers['Content-Type']) ? $this->headers['Content-Type'] : 'text/html';
        $method = camel_case("{$this->method}-{$this->action}");
        $action = [$this, $method];

        try
        {
            $vars = DIContainer::getMethodVars(get_class($this), $method);
            $params = array_merge($vars, [
                $request, $response, $args
            ]);
            $response = call_user_func_array($action, $params);
            $response = $response->withHeader('Content-Type', $contentType);
            return $response;
        }
        catch (Exception $e)
        {
            return $response->withStatus(400)->withJson([
                'code' => -1,
                'message' => RUNNING_MODE !== 'production' ? $e->getMessage() : 'Invalid Method!',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * @throws
     */
    protected function isLoginRequired()
    {
        return false;
    }

    /**
     * @throws
     */
    public function __call($method, $params)
    {
        $vars = DIContainer::getMethodVars(get_class($this->presenter), $method);
        return call_user_func_array([$this->presenter, $method], array_merge($vars, $params));
    }
}
