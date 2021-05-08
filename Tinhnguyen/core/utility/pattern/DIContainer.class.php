<?php

namespace Lza\LazyAdmin\Utility\Pattern;


use ErrorException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Dependencies Injection Container holds the dependencies and inject them to any classes which need them
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DIContainer
{
    const VARIABLE = '@var';
    const SINGLETON = '@singleton';

    private static $map;

    /**
     * @throws
     */
    public static function resolve()
    {
        $argCount = func_num_args();
        if ($argCount === 0)
        {
            throw new ErrorException("DIContainer: missing Class Name.");
        }

        $arguments = func_get_args();
        $className = $arguments[0];
        array_shift($arguments);
        if (!class_exists($className))
        {
            throw new ErrorException("DIContainer: missing class '{$className}'.");
        }

        $lines = [];
        $class = new ReflectionClass($className);
        self::getDocs($class, $lines);

        $isSingleton = false;
        foreach ($lines as $line)
        {
            if (strpos($line, self::SINGLETON) !== false)
            {
                $isSingleton = true;
                break;
            }
        }

        $object = $isSingleton
                ? call_user_func_array([$className, 'getInstance'], $arguments)
                : $class->newInstanceWithoutConstructor();

        $object = self::inject($object, $lines);
        if (!$isSingleton && $class->hasMethod('__construct'))
        {
            call_user_func_array([$object, '__construct'], $arguments);
        }
        return $object;
    }

    /**
     * @throws
     */
    private static function getDocs($class, &$lines)
    {
        $doc = $class->getDocComment();
        $lines = array_merge(explode("\n", $doc), $lines);
        while ($parent = $class->getParentClass())
        {
            $class = $parent;
            $name = $class->getName();
            if (strpos($name, "Lza\\") !== false)
            {
                self::getDocs($parent, $lines);
            }
        }
        foreach ($class->getInterfaces() as $interface)
        {
            $class = $interface;
            $name = $interface->getName();
            if (strpos($name, "Lza\\") !== false)
            {
                self::getDocs($interface, $lines);
            }
        }
    }

    /**
     * @throws
     */
    private static function inject($object, $docLines)
    {
        if (!count($docLines))
        {
            return $object;
        }

        foreach ($docLines as $line)
        {
            if (count($parts = explode(self::VARIABLE, $line)) <= 1)
            {
                continue;
            }

            if (count($parts = explode(" ", $parts[1])) <= 1)
            {
                continue;
            }

            $key = $parts[1];
            $key = str_replace("\n", "", $key);
            $key = str_replace("\r", "", $key);

            if (!isset(self::$map->$key))
            {
                continue;
            }

            switch (self::$map->$key->type)
            {
                case "value":
                    if (!isset($object->$key))
                    {
                        $object->$key = self::$map->$key->value;
                    }
                    break;
                case "class":
                    if (!isset($object->$key))
                    {
                        $object->$key = call_user_func_array(
                            ['self', 'resolve'],
                            array_merge(
                                [self::$map->$key->value],
                                self::$map->$key->arguments
                            )
                        );
                    }
                    break;
                case "singleton":
                    if (!isset($object->$key))
                    {
                        $object->$key = isset(self::$map->$key->instance)
                            ? self::$map->$key->instance
                            : self::$map->$key->instance = call_user_func_array(
                                ['self', 'resolve'],
                                array_merge(
                                    [self::$map->$key->value],
                                    self::$map->$key->arguments
                                )
                            );
                    }
                    break;
            }
        }

        return $object;
    }

    /**
     * @throws
     */
    public static function bindClass()
    {
        $argCount = func_num_args();
        if ($argCount < 2)
        {
            throw new ErrorException("DIContainer: missing Variable or Class Name.");
        }

        $arguments = func_get_args();
        $varName = $arguments[0];
        $className = $arguments[1];
        array_shift($arguments);
        array_shift($arguments);

        if (!strlen($varName))
        {
            throw new ErrorException("DIContainer: Variable or Name.");
        }

        if (!class_exists($className))
        {
            throw new ErrorException("DIContainer: missing class '{$className}'.");
        }

        self::addToMap($varName, (object) [
            "type" => "class",
            "value" => $className,
            "arguments" => $arguments
        ]);
    }

    /**
     * @throws
     */
    public static function bindSingleton()
    {
        $argCount = func_num_args();
        if ($argCount < 2)
        {
            throw new ErrorException("DIContainer: missing Variable or Class Name.");
        }

        $arguments = func_get_args();
        $varName = $arguments[0];
        $className = $arguments[1];
        array_shift($arguments);
        array_shift($arguments);

        self::addToMap($varName, (object) [
            "type" => "singleton",
            "value" => $className,
            "arguments" => $arguments
        ]);
    }

    /**
     * @throws
     */
    public static function bindValue($key, $value)
    {
        self::addToMap($key, (object) [
            "type" => "value",
            "value" => $value
        ]);
    }

    /**
     * @throws
     */
    public static function bindObject()
    {
        $argCount = func_num_args();
        if ($argCount < 2)
        {
            throw new ErrorException("DIContainer: missing Variable or Class Name.");
        }

        $arguments = func_get_args();
        $varName = $arguments[0];
        array_shift($arguments);

        $object = call_user_func_array(['self', 'resolve'], $arguments);
        self::bindValue($varName, $object);
        return $object;
    }

    /**
     * @throws
     */
    private static function addToMap($key, $object)
    {
        if (self::$map === null)
        {
            self::$map = (object) [];
        }
        self::$map->$key = $object;
    }

    /**
     * @throws
     */
    public static function getMethodVars($class, $method)
    {
        $function = new ReflectionMethod($class, $method);
        $doc = $function->getDocComment();
        $lines = explode("\n", $doc);
        $vars = [];
        foreach ($lines as $line)
        {
            if (count($parts = explode(self::VARIABLE, $line)) <= 1)
            {
                continue;
            }

            if (count($parts = explode(" ", $parts[1])) <= 1)
            {
                continue;
            }

            $key = $parts[1];
            $key = str_replace("\n", "", $key);
            $key = str_replace("\r", "", $key);

            if ($key !== '')
            {
                $vars[] = self::resolve($key);
            }
        }
        return $vars;
    }
}
