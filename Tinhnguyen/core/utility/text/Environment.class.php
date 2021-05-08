<?php

namespace Lza\LazyAdmin\Utility\Text;


/**
 * Environment stores environment variables
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Environment
{
    /**
     * @throws
     */
    public function __construct()
    {
        
    }

    /**
     * @throws
     */
    public function __get($key)
    {
        $key = camel_case($key);
        $key = strlen($key) > 0 ? $key : '_';
        return isset($this->$key) ? $this->$key : '';
    }

    /**
     * @throws
     */
    public function __set($key, $value)
    {
        $key = camel_case($key);
        $key = strlen($key) > 0 ? $key : '_';
        $this->$key = $value;
    }

    /**
     * @throws
     */
    public function __isset($key)
    {
        $key = camel_case($key);
        $key = strlen($key) > 0 ? $key : '_';
        return isset($this->$key);
    }

    /**
     * @throws
     */
    public function __unset($key)
    {
        $key = camel_case($key);
        $key = strlen($key) > 0 ? $key : '_';
        unset($this->$key);
    }
}
