<?php

namespace Lza\LazyAdmin\Runtime;


use Lza\LazyAdmin\Utility\Data\DatabasePool;

/**
 * Base Controller
 * Abstract class for specific module controllers
 * Handles all wriring tasks to database
 *
 * @var encryptor
 * @var i18n
 * @var model
 * @var module
 * @var session
 * @var sql
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class BaseController
{
    /**
     * @var DatabasePool
     */
    protected $database;

    /**
     * @throws
     */
    public function __construct()
    {
        $this->database = DatabasePool::getDatabase();
    }

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return $this->model->getTable(
            $module === null ? $this->module : $module
        );
    }

    /**
     * @throws
     */
    public function getView($module = null)
    {
        return $this->model->getView(
            $module === null ? $this->module : $module
        );
    }
}
