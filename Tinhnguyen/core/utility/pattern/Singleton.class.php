<?php

namespace Lza\LazyAdmin\Utility\Pattern;


/**
 * Singleton trait
 * To be used in Singleton classes
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait Singleton
{
    /**
     * @throws
     */
    public static function getInstance()
    {
        static $instance = null;
        $class = __CLASS__;
        return $instance ?: $instance = new $class;
    }

    /**
     * @throws
     */
    public function __clone()
    {
        trigger_error('Cloning ' . __CLASS__ . ' is not allowed.', E_USER_ERROR);
    }

    /**
     * @throws
     */
    public function __wakeup()
    {
        trigger_error('Unserializing ' . __CLASS__ . ' is not allowed.', E_USER_ERROR);
    }
}
