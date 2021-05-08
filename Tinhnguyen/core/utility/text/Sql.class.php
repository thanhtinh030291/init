<?php

namespace Lza\LazyAdmin\Utility\Text;


use Lza\LazyAdmin\Utility\Data\DatabasePool;
use PDO;

/**
 * Sql stores Sql queries
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Sql
{
    /**
     * @var string Path to store the SQLs
     */
    private $path = null;

    /**
     * @var array List of the SQL statements
     */
    private $sqls = [];

    /**
     * @var array List of the Escape Character
     */
    private static $escapeCharacters = [
        'mysql' => ['`', '`'],
        'dblib' => ['[', ']'],
        'mssql' => ['[', ']'],
        'sqlserver' => ['[', ']'],
        'oci' => ['"', '"'],
        'oracle' => ['"', '"'],
        'pgsql' => ['"', '"'],
        'sqlite' => ['"', '"']
    ];

    /**
     * @throws
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @throws
     */
    public function __get($key)
    {
        global $ds;
        $this->sqls[$key] = isset($this->sqls[$key])
            ? $this->sqls[$key]
            : file_get_contents(
                $this->path . $ds . snake_case($key) . '.sql'
            );
        return $this->escape($this->sqls[$key]);
    }

    /**
     * @throws
     */
    public function __call($method, $params)
    {
        global $ds;
        $this->sqls[$method] = isset($this->sqls[$method])
            ? $this->sqls[$method]
            : file_get_contents(
                $this->path . $ds . snake_case($method) . '.sql'
            );

        $sql = $this->sqls[$method];
        if (count($params) === 1 && is_array($params))
        {
            foreach ($params[0] as $key => $value)
            {
                $sql = str_replace("{{$key}}", $value, $sql);
            }
        }
        else
        {
            $sql = call_user_func_array('sprintf', array_merge([$sql], $params));
        }
        return $this->escape($sql);
    }

    /**
     * @throws
     */
    public static function start($key = MAIN_DATABASE)
    {
        if (TRANSACTION)
        {
            $connection = DatabasePool::getConnection($key);
            $connection->query('SET autocommit = 0');
            $connection->query('BEGIN');
        }
    }

    /**
     * @throws
     */
    public static function commit($key = MAIN_DATABASE)
    {
        if (TRANSACTION)
        {
            $connection = DatabasePool::getConnection($key);
            $connection->query('COMMIT');
            $connection->query('SET autocommit = 1');
        }
    }

    /**
     * @throws
     */
    public static function rollback($key = MAIN_DATABASE)
    {
        if (TRANSACTION)
        {
            $connection = DatabasePool::getConnection($key);
            $connection->query('ROLLBACK');
            $connection->query('SET autocommit = 1');
        }
    }

    /**
     * @throws
     */
    public static function query($sql, $params = [], $key = MAIN_DATABASE)
    {
        $sql = self::escape($sql, $key);
        $connection = DatabasePool::getConnection($key);
        $stmt = $connection->prepare($sql);
        if (!$stmt)
        {
            return false;
        }

        if (!$stmt->execute($params))
        {
            return false;
        }

        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @throws
     */
    public static function escape($sql, $key = MAIN_DATABASE)
    {
        $info = DatabasePool::getDatabaseInfo($key);
        $sql = " {$sql} ";

        $begin = self::$escapeCharacters[$info['database_info']['type']][0];
        $end = self::$escapeCharacters[$info['database_info']['type']][1];

        $sql = str_replace(' `', " {$begin}", $sql);
        $sql = str_replace('(`', "({$begin}", $sql);
        $sql = str_replace('.`', ".{$begin}", $sql);
        $sql = str_replace(',`', ",{$begin}", $sql);
        $sql = str_replace('=`', "={$begin}", $sql);
        $sql = str_replace('>`', ">{$begin}", $sql);
        $sql = str_replace('<`', "<{$begin}", $sql);
        $sql = str_replace("\t`", "\t{$begin}", $sql);
        $sql = str_replace("\r\n`", "\r\n{$begin}", $sql);
        $sql = str_replace("\n`", "\n{$begin}", $sql);

        $sql = str_replace('` ', "{$end} ", $sql);
        $sql = str_replace('`)', "{$end})", $sql);
        $sql = str_replace('`.', "{$end}.", $sql);
        $sql = str_replace('`,', "{$end},", $sql);
        $sql = str_replace('`=', "{$end}=", $sql);
        $sql = str_replace('`>', "{$end}>", $sql);
        $sql = str_replace('`<', "{$end}<", $sql);
        $sql = str_replace("`\t", "{$end}\t", $sql);
        $sql = str_replace("`\r\n", "{$end}\r\n", $sql);
        $sql = str_replace("`\n", "{$end}\n", $sql);

        return $sql;
    }
}
