<?php

namespace Lza\LazyAdmin\Utility\Tool;


use ReflectionClass;

/**
 * Reflection helps create object and call method of unaccessible or undefined classes
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Reflection
{
    /**
     * @throws
     */
    public static function newInstance($className, $args = [])
    {
        $class = new ReflectionClass($className);
        $instance = $class->newInstanceWithoutConstructor();

        $constructor = $class->getConstructor();
        if ($constructor !== null)
        {
            $constructor->setAccessible(true);
            $constructor->invokeArgs($instance, $args);
        }

        return $instance;
    }
}
