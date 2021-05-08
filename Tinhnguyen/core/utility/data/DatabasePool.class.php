<?php

namespace Lza\LazyAdmin\Utility\Data;


use Lza\LazyAdmin\Utility\Tool\Log\AppLogger;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;
use NotORM;
use NotORM_Structure_Convention as NotOrmStructureConvention;
use PDO;
use PDOOCI\PDO as PDOOCI;

/**
 * Databse Factory
 * Provide the ORM to connect to databases
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DatabasePool
{
    private static $connections = [];
    private static $databases = [];
    private static $databasesInfo;

    /**
     * @throws
     */
    public static function getDatabase($key = MAIN_DATABASE)
    {
        if (!isset(self::$databases[$key]))
        {
            $info = self::getDatabaseInfo($key);
            $connection = self::getConnection($key);
            $structure = new NotOrmStructureConvention(
                $info['table_info']['pkey'],
                $info['table_info']['fkey'],
                $info['table_info']['tnme'],
                $info['table_info']['prfx']
            );
            self::$databases[$key] = new NotORM($connection, $structure);

            if (DEBUG_QUERY)
            {
                self::$databases[$key]->debug = function($query, $parameters)
                {
                    AppLogger::getInstance()->log(
                        LogLevel::DEBUG, "Query: {$query} with params: " . json_encode($parameters)
                    );
                };
            }
        }
        return self::$databases[$key];
    }

    /**
     * @throws
     */
    public static function getConnection($key = MAIN_DATABASE)
    {
        if (!isset(self::$connections[$key]))
        {
            $info = self::getDatabaseInfo($key);
            if ($info['database_info']['type'] === 'oracle')
            {
                $connString = "//{$info['database_info']['host']}:"
                            . "{$info['database_info']['port']}/"
                            . "{$info['database_info']['name']};"
                            . "charset={$info['database_info']['chst']}";
                $connection = new PDOOCI(
                    $connString,
                    $info['database_info']['user'],
                    $info['database_info']['pass']
                );
            }
            else
            {
                $connString = "{$info['database_info']['type']}:"
                            . "host={$info['database_info']['host']};"
                            . "port={$info['database_info']['port']};"
                            . "dbname={$info['database_info']['name']};"
                            . "charset={$info['database_info']['chst']}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => $info['database_info']['emulate_prepares']
                ];
                if (isset($info['database_info']['cert']))
                {
                    global $ds;
                    $path = dirname(dirname(dirname(__DIR__))) . "{$ds}config{$ds}assets{$ds}";
                    $options[PDO::MYSQL_ATTR_SSL_CA] = $path . $info['database_info']['cert'];
                    $options[PDO::MYSQL_ATTR_SSL_CIPHER] = 'TLSv1.2';
                }
                if (isset($info['database_info']['verify']))
                {
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $info['database_info']['verify'];
                }
                $connection = new PDO(
                    $connString,
                    $info['database_info']['user'],
                    $info['database_info']['pass'],
                    $options
                );
                foreach ($info['options'] as $option)
                {
                    $connection->exec($option);
                }
            }

            self::$connections[$key] = $connection;
        }
        return self::$connections[$key];
    }

    /**
     * @throws
     */
    public static function getDatabaseInfo($key)
    {
        self::$databasesInfo = isset(self::$databasesInfo)
             ? self::$databasesInfo
             : json_decode(DATABASES, true);
        return self::$databasesInfo[$key];
    }
}
