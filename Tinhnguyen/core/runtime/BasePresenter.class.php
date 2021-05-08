<?php

namespace Lza\LazyAdmin\Runtime;


use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;

/**
 * Base Presenter
 * Abstract class for specific view presenters
 * Handles all actions
 *
 * @var controller
 * @var csrf
 * @var datetime
 * @var encryptor
 * @var env
 * @var i18n
 * @var logger
 * @var mailer
 * @var module
 * @var request
 * @var session
 * @var setting
 * @var sql
 * @var validator
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class BasePresenter
{
    /**
     * @var BaseView MVP View that holds this control
     */
    protected $viewer;

    /**
     * @var object Data to be used in the layouts
     */
    protected $data;

    /**
     * @throws
     */
    public function __construct()
    {
        $this->data = (object) [];
        $this->data->infoAlert = '';
        $this->data->successAlert = '';
        $this->data->debugAlert = '';
        $this->data->warningAlert = '';
        $this->data->errorAlert = '';
        $this->database = DatabasePool::getDatabase();
    }

    /**
     * @throws
     */
    public function getViewer()
    {
        return $this->viewer;
    }

    /**
     * @throws
     */
    public function setViewer($viewer)
    {
        $this->viewer = $viewer;
    }

    /**
     * @throws
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @throws
     */
    public function doGetTable($module)
    {
        return $this->getTable($module);
    }

    /**
     * @throws
     */
    public function doGetView($module)
    {
        return $this->getView($module);
    }

    /**
     * @throws
     */
    public function assign($key, $value)
    {
        $this->viewer->assign($key, $value);
    }

    /**
     * @throws
     */
    public function doGetTableFields($module, $conditions = [])
    {
        return $this->getTableFields($module, $conditions);
    }

    /**
     * @throws
     */
    public function doGetAllTableFields($module)
    {
        return $this->getAllTableFields($module);
    }

    /**
     * @throws
     */
    public function onValidateSuccess($data = null)
    {
        // TODO: implement if needed
    }

    /**
     * @throws
     */
    public function onValidateError($message)
    {
        $this->logger->log(LogLevel::ERROR, $message);
    }

    /**
     * @throws
     */
    public function __call($method, $params)
    {
        $vars = DIContainer::getMethodVars(get_class($this->controller), $method);
        return call_user_func_array([$this->controller, $method], array_merge($vars, $params));
    }
}
