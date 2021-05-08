<?php

namespace Lza\Config\Models;


use Lza\Config\Models\BaseModel;
use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Model Pool
 * Create models if needed
 * Stores the models
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ModelPool
{
    private static $instances = [];

    /**
     * @throws
     */
    public static function __callStatic($name, $args)
    {
        DatabasePool::$name($args);
    }

    /**
     * @throws
     */
    public static function getModel($module = null, $db = null)
    {
        $ds = DIRECTORY_SEPARATOR;
        $class = camel_case($module, true) . 'Model';
        if (file_exists(__DIR__ . "{$ds}{$class}.class.php"))
        {
            $class = "\\" . __NAMESPACE__ . "\\{$class}";
            return DIContainer::resolve($class);
        }
        return self::$instances[$module] = isset(self::$instances[$module])
                ? self::$instances[$module]
                : DIContainer::resolve(BaseModel::class, $module, $db);
    }
}
