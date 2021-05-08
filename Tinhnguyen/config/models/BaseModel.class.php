<?php

namespace Lza\Config\Models;


use Exception;
use Lza\LazyAdmin\Exception\DatabaseException;
use Lza\LazyAdmin\Exception\DatabaseInsertException;
use Lza\LazyAdmin\Exception\DatabaseDeleteException;
use Lza\LazyAdmin\Exception\DatabaseUpdateException;
use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;
use PDO;

/**
 * Base Model
 * Transforms to specific models base on module
 * Access to Databases: CRUD
 *
 * @var env
 * @var logger
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class BaseModel
{
    /**
     * @var object Main Database
     */
    protected $mainDb;

    /**
     * @var object Module Database
     */
    protected $db;

    /**
     * @var object Module Database Info
     */
    protected $dbInfo;

    /**
     * @var string Database Table
     */
    protected $module;

    /**
     * @var array Table Information
     */
    protected $table;

    /**
     * @var array Table Fields Information
     */
    protected $tableFields;

    /**
     * @var string Database View
     */
    protected $view;

    /**
     * @throws
     */
    public function __construct($module = null, $db = null)
    {
        if ($module !== null)
        {
            $this->module = $module;
        }
        $this->mainDb = DatabasePool::getDatabase('main');
        $this->table = $db === null ? $this->getTable($module) : [
            'db_id' => $db,
            'id' => snake_case($module),
            'single' => $module,
            'settings' => [],
            'enabled' => true
        ];
        $this->db = DatabasePool::getDatabase($this->table['db_id']);
        $this->dbInfo = DatabasePool::getDatabaseInfo($this->table['db_id']);
    }

    /**
     * @throws
     */
    public function getDatabaseInfo()
    {
        return $this->dbInfo;
    }

    /**
     * @throws
     */
    public function __call($method, $params)
    {
        $table = snake_case($this->module);
        return call_user_func_array([$this->db->$table, $method], $params);
    }

    /**
     * @throws
     */
    public function where()
    {
        $table = snake_case($this->module);
        $args = func_get_args();
        if (count($args) === 1 && is_array($args[0]))
        {
            $params = [];
            $condition = [
                $this->joinConditions($args[0], $params)
            ];
            return call_user_func_array(
                [$this->db->$table, 'where'],
                array_merge($condition, $params)
            );
        }
        elseif (
            count($args) === 2 &&
            is_string($args[0]) &&
            strpos($args[0], '?') === false
        )
        {
            $params = [
                $args[1]
            ];
            $condition = [
                "{$args[0]} = ?"
            ];
            return call_user_func_array(
                [$this->db->$table, 'where'],
                array_merge($condition, $params)
            );
        }
        return call_user_func_array([$this->db->$table, 'where'], $args);
    }

    /**
     * @recursive
     *
     * @throws
     */
    private function joinConditions($condition, &$params, $type = 'and')
    {
        if (is_array($condition))
        {
            $type = strtoupper($type);
            if (array_is_assoc($condition))
            {
                foreach ($condition as $key => $value)
                {
                    unset($condition[$key]);
                    if (is_int($key))
                    {
                        $condition[] = $this->joinConditions($value, $params, $type);
                    }
                    elseif (in_array(strtolower($key), ['or', 'and']))
                    {
                        $condition[] = $this->joinConditions($value, $params, $key);
                    }
                    else
                    {
                        $key = explode(' ', $key);
                        $keyCount = count($key);
                        if ($keyCount == 1)
                        {
                            if (is_array($value))
                            {
                                $dbParams = [];
                                foreach ($value as $v)
                                {
                                    $dbParams[] = '?';
                                }
                                $params = array_merge($params, $value);
                                $condition[] = "({$key[0]} IN (" . implode(', ', $dbParams) . "))";
                            }
                            elseif ($value == null)
                            {
                                $condition[] = "({$key[0]} is null)";
                            }
                            else
                            {
                                $params[] = $value;
                                $condition[] = "({$key[0]} = ?)";
                            }
                        }
                        else
                        {
                            $operator = $key[$keyCount - 1];
                            if ($keyCount > 2)
                            {
                                if (strtolower($key[$keyCount - 2]) === 'not')
                                {
                                    $operator = $key[$keyCount - 2] . ' ' . $key[$keyCount - 1];
                                    unset($key[$keyCount - 1]);
                                }
                            }
                            unset($key[count($key) - 1]);

                            $operator = strtoupper($operator);
                            $key2 = implode(' ', $key);
                            if (strpos(strtolower($operator), 'between') !== false)
                            {
                                $params[] = $value[0];
                                $params[] = $value[1];
                                $condition[] = "({$key2} {$operator} ? AND ?)";
                            }
                            elseif (strtolower($operator) === 'cover')
                            {
                                $coverValues = explode(' and ', str_replace(' AND ', ' and ', $key2));
                                $params[] = $value;
                                $condition[] = "(? BETWEEN {$coverValues[0]} AND {$coverValues[1]})";
                            }
                            elseif (strtolower($operator) === 'not cover')
                            {
                                $coverValues = explode(' and ', str_replace(' AND ', ' and ', $key2));
                                $params[] = $value;
                                $condition[] = "(? NOT BETWEEN {$coverValues[0]} AND {$coverValues[1]})";
                            }
                            elseif (strtolower($operator) === 'by')
                            {
                                $key2 = str_replace([' COVER ', ' NOT '], [' cover ', ' not '], $key2);
                                $coverValues = strpos($key2, ' not ') === false
                                        ? explode(' cover ', $key2)
                                        : explode(' not cover ', $key2);
                                $params[] = $value;
							    $operator = strpos($key2, ' not ') === false
                                        ? 'BETWEEN'
                                        : 'NOT BETWEEN';
                                $condition[] = "({$coverValues[1]} {$operator} ? AND {$coverValues[0]})";
                            }
                            elseif (strtolower($operator) === 'in')
                            {
                                $dbParams = [];
                                foreach ($value as $v)
                                {
                                    $dbParams[] = '?';
                                }
                                $params = array_merge($params, $value);
                                $condition[] = "({$key2} {$operator} (" . implode(', ', $dbParams) . "))";
                            }
                            elseif (
                                strpos(strtolower($value), 'select') !== false &&
                                strpos(strtolower($value), 'from') !== false
                            )
                            {
                                $condition[] = "({$key2} {$operator} ({$value}))";
                            }
                            elseif ($operator === '=' && $value == null)
                            {
                                $condition[] = "({$key2} is null)";
                            }
                            else
                            {
                                $params[] = $value;
                                $condition[] = "({$key2} {$operator} ?)";
                            }
                        }
                    }
                }
                $condition = '(' . implode(" {$type} ", $condition) . ')';
            }
            else
            {
                foreach ($condition as $key => &$condition2)
                {
                    $condition2 = $this->joinConditions($condition2, $params, is_int($key) ? $type : $key);
                }
                $condition = '(' . implode(" {$type} ", $condition) . ')';
            }
        }
        elseif (is_bool($condition))
        {
            $params[] = $condition;
            $condition = '(?)';
        }

        return $condition;
    }


    /**
     * @throws
     */
    public function getList(
        $page = null, $size = null, $fields = null,
        $conditions = null, $orders = null, $level = null
    )
    {
        $result = $this->where(isset($conditions) ? $conditions : '1=1');
        $result = $orders === null ? $result : $result->order($orders);
        $result = $page === null ? $result : $result->limit($size, ($page - 1) * $size);
        $result = $fields === null ? $result : $result->select($fields);

        return $result;
    }

    /**
     * @throws
     */
    public function getListHistory($ids = null)
    {
        $table = snake_case($this->module) . '_history';

        $result = $this->db->$table();
        $result = isset($ids) ? $result->where('id', $ids) : $result;
        $result = $result->order("id, valid_from");

        return $result;
    }

    /**
     * @throws
     */
    public function getRevision($id = null)
    {
        $table = snake_case($this->module) . '_history';
        $items = $this->db->$table('valid_to is null');
        $items = $items->select('CAST(UNIX_TIMESTAMP(valid_from) * 1000000 AS int) as `revision`');
        $items = $id === null ? $items : $items->where('id', $id);
        $item = $items->fetch();
        return intval($item['revision']);
    }

    /**
     * @throws
     */
    public function get($id, $level = null)
    {
        $columns = $this->getTableFieldsString();

        $result = $this->where('id', $id);
        $result = isset($columns) ? $result->select($columns) : $result;
        $result = $result->limit(1);

        return $result;
    }

    /**
     * @throws
     */
    public function getItemHistory($id)
    {
        $table = snake_case($this->module) . '_history';
        $columns = $this->getTableFields($this->module);
        $selection = "
            CAST(UNIX_TIMESTAMP(valid_from) * 1000000 AS int) `id`,
            valid_from `valid_from`,
            action `action`
        ";
        foreach ($columns as $column)
        {
            if ($column['field'] !== 'id')
            {
                $belongFields = ['belong', 'weakbelong'];
                if (in_array($column['type'], $belongFields))
                {
                    $selection .= ",{$column['field']}_id `{$column['field']}_id`";
                }
                else
                {
                    $selection .= ",{$column['field']} `{$column['field']}`";
                }
            }
        }

        $result = $this->db->$table("id", $id);
        $result->select($selection);
        $result->order("valid_from DESC");

        $items = [];
        $no = count($result);
        foreach ($result as $item)
        {
            $item['id'] = $no;
            $items[] = $item;
            $no--;
        }

        return $items;
    }

    /**
     * @throws
     */
    public function create($user, $item, $many = null)
    {
        $item['crt_by'] = $user;
        foreach ($item as $key => $value)
        {
            if (strpos($key, '`') === false)
            {
                $item["`{$key}`"] = $value;
                unset($item[$key]);
            }
        }

        $result = $this->insert($item);
        if ($result === false)
        {
            throw new DatabaseInsertException(
                "Failed to insert item to {$this->module}:" . json_encode($item)
            );
        }
        if ($many !== null)
        {
            $this->updateManyToManyReference($result['id'], $many);
        }
        return $result;
    }

    /**
     * @throws
     */
    public function modify($user, $original, $changed, $many = null)
    {
        $changed['upd_by'] = $user;
        $result = count($changed) > 0 ? $original->update($changed) : null;
        if ($result === false)
        {
            throw new DatabaseUpdateException(
                "Failed to update item in {$this->module}:" . json_encode($changed)
            );
        }
        if ($result === null && $this->updateManyToManyReference($original['id'], $many))
        {
            return $original;
        }
        elseif ($result === null && count($changed) === 0)
        {
            return $original;
        }
        elseif ($result)
        {
            $this->updateManyToManyReference($original['id'], $many);
        }
        return $original;
    }

    /**
     * @throws
     */
    protected function updateManyToManyReference($id, $many = null)
    {
        if ($many === null)
        {
            return false;
        }
        $tableModuleName = snake_case($this->module);
        $tableIdModule = snake_case($this->module) . '_id';

        $result = false;
        foreach ($many as $table => $manyItems)
        {
            $result = true;
            $tableIdRefence = trim(str_replace($tableModuleName, '', $table), '_') . '_id';
            $insertingItems = [];
            foreach ($manyItems as $manyItem)
            {
                $insertingItems[] = [
                    $tableIdModule => intval($id),
                    $tableIdRefence => intval($manyItem)
                ];
            }

            $this->db->$table($tableIdModule, $id)->delete();
            $this->db->$table()->insert_multi($insertingItems);
        }
        return $result;
    }

    /**
     * @throws
     */
    public function remove($user, $id)
    {
        $original = $this->get($id)->fetch();
        if ($original)
        {
            $many = [];
            if (!isset($this->tableFields))
            {
                $this->tableFields = $this->getTableFields();
            }

            foreach ($this->tableFields as $field)
            {
                if ($field['type'] === 'have')
                {
                    $many[$field['field']] = [];
                }
            }

            $this->updateManyToManyReference($id, $many);
            if ($this->where("id", $id)->delete())
            {
                return true;
            }
        }
        throw new DatabaseDeleteException(
            "Failed to delete {$this->module}: {$id}"
        );
    }

    /**
     * @throws
     */
    public function removeAll($user, $ids)
    {
        foreach ($ids as $id)
        {
            $this->remove($user, $id);
        }
    }

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        if (isset($this->table))
        {
            return $this->table;
        }

        if ($view = $this->mainDb->lzaview("name", $module)->fetch())
        {
            $module = $view->lzamodule['name'];
        }
        $table = snake_case($module);

        $result = [];
        $tableInfos = $this->mainDb->lzamodule("lzamodule.id", $table);
        $tableInfo = $tableInfos->fetch();
        if (!$tableInfo)
        {
            return [
                'db_id' => 'main',
                'id' => snake_case($module),
                'single' => $module,
                'settings' => [],
                'enabled' => false
            ];
        }
        foreach ($tableInfo as $key => $value)
        {
            $result[$key] = htmlspecialchars_decode($value, 2);
        }

        return $result;
    }

    /**
     * @throws
     */
    public function getView($module = null)
    {
        if (isset($this->view))
        {
            return $this->view;
        }

        $view = trim(
            strtolower(
                preg_replace(
                    '/([a-z0-9])?([A-Z])/', '$1_$2',
                    $module === null ? $this->module : $module
                )
            ),
            '_'
        );

        $views = $this->mainDb->lzaview(
            "lzaview.name = ? and
             lzamodule.id is not null",
            $view
        );
        return $views->select("lzamodule.id")->fetch();
    }

    /**
     * @throws
     */
    public function getAllTableFields($module = null)
    {
        if (isset($this->allTableFields))
        {
            return $this->allTableFields;
        }

        $table = trim(
            strtolower(
                preg_replace(
                    '/([a-z0-9])?([A-Z])/', '$1_$2',
                    $module === null ? $this->module : $module
                )
            ),
            '_'
        );

        $result = $this->mainDb->lzafield("lzamodule.id", $table);
        $selections = [
            'lzamodule.db_id `db_id`',
            'lzamodule.id `table`',
            'lzafield.note `note`',
            'lzafield.id `id`',
            'lzafield.field `field`',
            'lzafield.type `type`',
            'lzafield.mandatory `mandatory`',
            'lzafield.is_unique `unique`',
            'lzafield.minlength `minlength`',
            'lzafield.maxlength `maxlength`',
            'lzafield.regex `regex`',
            'lzafield.error `error`',
            'lzafield.order_by `order`',
            'lzafield.level `level`',
            'lzafield.statistic `statistic`',
            'lzafield.display `display`'
        ];
        foreach ($this->mainDb->lzalanguage() as $language)
        {
            $selections[] = "lzafield.single{$language['code']} `single{$language['code']}`";
            $selections[] = "lzafield.plural{$language['code']} `plural{$language['code']}`";
        }

        $result = $result->select(implode(',', $selections));
        $result = $result->order("lzafield.order_by");
        $fields = [];
        foreach ($result as $column)
        {
            $field = [
                'db_id' => htmlspecialchars_decode($column['db_id'], 2),
                'table' => htmlspecialchars_decode($column['table'], 2),
                'field_note' => htmlspecialchars_decode($column['note'], 2),
                'id' => htmlspecialchars_decode($column['id'], 2),
                'field' => htmlspecialchars_decode($column['field'], 2),
                'type' => htmlspecialchars_decode($column['type'], 2),
                'mandatory' => htmlspecialchars_decode($column['mandatory'], 2),
                'unique' => htmlspecialchars_decode($column['unique'], 2),
                'minlength' => htmlspecialchars_decode($column['minlength'], 2),
                'maxlength' => htmlspecialchars_decode($column['maxlength'], 2),
                'regex' => htmlspecialchars_decode($column['regex'], 2),
                'error' => htmlspecialchars_decode($column['error'], 2),
                'order' => htmlspecialchars_decode($column['order'], 2),
                'level' => htmlspecialchars_decode($column['level'], 2),
                'statistic' => htmlspecialchars_decode($column['statistic'], 2),
                'display' => htmlspecialchars_decode($column['display'], 2)
            ];
            foreach ($this->mainDb->lzalanguage() as $language)
            {
                $field["single{$language['code']}"] = htmlspecialchars_decode(
                    $column["single{$language['code']}"], 2
                );
                $field["plural{$language['code']}"] = htmlspecialchars_decode(
                    $column["plural{$language['code']}"], 2
                );
            }
            if ($field['type'] === 'enum')
            {
                $query = DatabasePool::getConnection()->prepare("
                    SHOW COLUMNS
                    FROM {$table}
                    WHERE Field = ?
                ");
                try
                {
                    $query->execute([$field['field']]);
                    $types = $query->fetchAll(PDO::FETCH_COLUMN | \PDO::FETCH_UNIQUE, 1);
                    $types = $types[$field['field']];
                    $field['display'] = json_encode(explode("','", trim($types, "enum()'")));
                }
                catch (Exception $e)
                {
                    $this->logger->log(LogLevel::DEBUG, $e->getMessage());
                }
            }
            $fields[] = $field;
        }
        $this->allTableFields = $fields;
        return $fields;
    }

    /**
     * @throws
     */
    public function getTableFields($module = null, $conditions = [])
    {
        $view = $this->env->view;
        $action = $this->env->action;
        $level = $this->env->level;

        if (isset($_SESSION['user']['user_filter'][$this->env->module]) && $view === 'Listall')
        {
            $filters = $this->mainDb->lzafilter('id', $_SESSION['user']['user_filter'][$this->env->module]);
            $filter = $filters->fetch();
            $conditions[] = 'lzafield.id in (' .
                implode(',', json_decode($filter['selections'], true)) .
            ')';
        }
        elseif (isset($level))
        {
            $conditions[] = "level & {$level} = {$level}";
        }
        $table = trim(
            strtolower(
                preg_replace(
                    '/([a-z0-9])?([A-Z])/', '$1_$2',
                    $module === null ? $this->module : $module
                )
            ),
            '_'
        );

        $conditions[] = "lzamodule.id = '$table'";

        $result = $this->mainDb->lzafield();
        $selections = [
            'lzamodule.db_id `db_id`',
            'lzamodule.id `table`',
            'lzafield.note `note`',
            'lzafield.id `id`',
            'lzafield.field `field`',
            'lzafield.type `type`',
            'lzafield.mandatory `mandatory`',
            'lzafield.is_unique `unique`',
            'lzafield.minlength `minlength`',
            'lzafield.maxlength `maxlength`',
            'lzafield.regex `regex`',
            'lzafield.error `error`',
            'lzafield.order_by `order`',
            'lzafield.level `level`',
            'lzafield.statistic `statistic`',
            'lzafield.display `display`'
        ];
        foreach ($this->mainDb->lzalanguage() as $language)
        {
            $selections[] = "lzafield.single{$language['code']} `single{$language['code']}`";
            $selections[] = "lzafield.plural{$language['code']} `plural{$language['code']}`";
        }

        $result = $result->select(implode(',', $selections));
        $result = $result->order("lzafield.order_by");
        $result = $result->where(implode(' and ', $conditions));

        $fields = [];
        foreach ($result as $column)
        {
            $field = [
                'db_id' => htmlspecialchars_decode($column['db_id'], 2),
                'table' => htmlspecialchars_decode($column['table'], 2),
                'field_note' => htmlspecialchars_decode($column['note'], 2),
                'id' => htmlspecialchars_decode($column['id'], 2),
                'field' => htmlspecialchars_decode($column['field'], 2),
                'type' => htmlspecialchars_decode($column['type'], 2),
                'mandatory' => htmlspecialchars_decode($column['mandatory'], 2),
                'unique' => htmlspecialchars_decode($column['unique'], 2),
                'minlength' => htmlspecialchars_decode($column['minlength'], 2),
                'maxlength' => htmlspecialchars_decode($column['maxlength'], 2),
                'regex' => htmlspecialchars_decode($column['regex'], 2),
                'error' => htmlspecialchars_decode($column['error'], 2),
                'order' => htmlspecialchars_decode($column['order'], 2),
                'level' => htmlspecialchars_decode($column['level'], 2),
                'statistic' => htmlspecialchars_decode($column['statistic'], 2),
                'display' => htmlspecialchars_decode($column['display'], 2)
            ];
            foreach ($this->mainDb->lzalanguage() as $language)
            {
                $field["single{$language['code']}"] = htmlspecialchars_decode(
                    $column["single{$language['code']}"], 2
                );
                $field["plural{$language['code']}"] = htmlspecialchars_decode(
                    $column["plural{$language['code']}"], 2
                );
            }
            if ($field['type'] === 'enum')
            {
                $query = DatabasePool::getConnection()->prepare(
                    "SHOW COLUMNS FROM $table WHERE Field = ?"
                );
                try
                {
                    $query->execute([$field['field']]);
                    $types = $query->fetchAll(PDO::FETCH_COLUMN | \PDO::FETCH_UNIQUE, 1);
                    $types = $types[$field['field']];
                    $field['display'] = json_encode(
                        explode(",", str_replace(['enum', '(', ')', "'"], '', $types))
                    );
                }
                catch (Exception $e)
                {
                    $this->logger->log(LogLevel::DEBUG, $e->getMessage());
                }
            }
            $fields[$column['id']] = $field;
        }

        if (isset($filter))
        {
            $result = [];
            foreach (json_decode($filter['selections'], true) as $selection)
            {
                foreach ($fields as $field)
                {
                    if ($selection === $field['id'])
                    {
                        $result[] = $field;
                        break;
                    }
                }
            }
        }
        else
        {
            $result = $fields;
        }

        $this->tableFields = $result;
        return $result;
    }

    /**
     * @throws
     */
    public function getTableFieldsString()
    {
        $level = $this->env->level;

        $columns = [];
        $table = $this->getTable($this->module);
        $tableFields = $this->getTableFields($this->module);
        if (count($tableFields) == 0)
        {
            throw new DatabaseException('No field selected');
        }

        foreach ($tableFields as $field)
        {
            switch ($field['type'])
            {
                case 'belong':
                case 'weakbelong':
                    $columns[] = "{$field['table']}.{$field['field']}_id `" . strtolower($field['field']) . '_id`';
                    break;
                case 'has':
                case 'have':
                    break;
                case 'date':
                    if ($this->dbInfo['database_info']['type'] === 'oracle')
                    {
                        $columns[] = "TO_CHAR({$field['table']}.{$field['field']}, 'YYYY-MM-DD') `"
                                  . strtolower($field['field']) . '`';
                    }
                    else
                    {
                        $columns[] = "{$field['table']}.{$field['field']} `" . strtolower($field['field']) . '`';
                    }
                    break;
                case 'datetime':
                case 'eventstart':
                case 'eventend':
                case 'datetime':
                    if ($this->dbInfo['database_info']['type'] === 'oracle')
                    {
                        $columns[] = "TO_CHAR({$field['table']}.{$field['field']}, 'YYYY-MM-DD HH24:II:SS') `"
                                  . strtolower($field['field']) . '`';
                    }
                    else
                    {
                        $columns[] = "{$field['table']}.{$field['field']} `" . strtolower($field['field']) . '`';
                    }
                    break;
                default:
                    $columns[] .= "{$field['table']}.{$field['field']} `" . strtolower($field['field']) . '`';
            }
        }

        $pkey = $this->dbInfo['table_info']['pkey'];
        if (!in_array("{$table['id']}.{$pkey} `{$pkey}`", $columns))
        {
            $columns[] = "{$table['id']}.{$pkey} `{$pkey}`";
        }
        return implode(',', $columns);
    }
}
