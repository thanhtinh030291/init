<?php

namespace Lza\LazyAdmin\Utility\Text;


use SplFileObject;

/**
 * Property is the class use to retrieve properties
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Property
{
    /**
     * @var array List to store Properties read from files
     */
    private $properties = [];

    /**
     * @throws
     */
    public function __construct($path, $alt)
    {
        $file = new SplFileObject($alt);
        while (!$file->eof())
        {
            $line = $file->fgets();
            $parts = explode('=', $line);
            $key = $parts[0];
            array_splice($parts, 0, 1);
            $value = implode('=', $parts);

            $this->properties[$key] = trim($value);
        }

        $path = str_replace('//', '/', $path);
        $file = new SplFileObject($path);
        while (!$file->eof())
        {
            $line = $file->fgets();
            $parts = explode('=', $line);
            $key = $parts[0];
            array_splice($parts, 0, 1);
            $value = implode('=', $parts);

            $this->properties[$key] = trim($value);
        }
    }

    /**
     * @throws
     */
    public function __get($key)
    {
        $key = snake_case($key);
        return isset($this->properties[$key]) ? $this->properties[$key] : $key;
    }

    /**
     * @throws
     */
    public function __call($method, $params)
    {
        $method = snake_case($method);
        return isset($this->properties[$method])
                ? call_user_func_array('sprintf', array_merge([$this->properties[$method]], $params))
                : $method;
    }
}
