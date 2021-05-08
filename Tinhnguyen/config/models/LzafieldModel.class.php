<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Lza Field Model
 * Access to lzafield table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LzafieldModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'lzafield';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'db_id' => 'main',
            'id' => 'lzafield',
            'single' => 'Field',
            'plural' => 'Fields',
            'single_vi' => 'Field',
            'plural_vi' => 'Fields',
            'display' => 'single',
            'enabled' => 'Yes',
            'icon' => 'edit',
            'sort' => '[1,"asc"]',
            'settings' => ''
        ];
    }

    /**
     * @throws
     */
    public function getAllTableFields($module = null, $conditions = [])
    {
        $module = snake_case($module);
        if (isset($this->tableFields))
        {
            return $this->tableFields;
        }

        $count = 1;
        $fields = [];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'id',
            'single' => 'Id',
            'plural' => 'Ids',
            'single_vi' => 'Id',
            'plural_vi' => 'Ids',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 1,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 2,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'lzamodule',
            'single' => 'Module',
            'plural' => 'Modules',
            'single_vi' => 'Module',
            'plural_vi' => 'Modules',
            'type' => 'belong',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => 'single',
            'display' => 'single'
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'field',
            'single' => 'Field',
            'plural' => 'Fields',
            'single_vi' => 'Field',
            'plural_vi' => 'Fields',
            'type' => 'text',
            'regex' => '^[a-z0-9_]*$',
            'error' => 'invalid_field',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 6,
            'statistic' => 'single',
            'display' => 'single'
        ];

        foreach ($this->mainDb->lzalanguage() as $language)
        {
            $count++;
            $fields[] = [
                'table' => "$module",
                'field_note' => "",
                'id' => $count,
                'field' => 'single' . $language['code'],
                'single' => 'Single ' . $language['name'],
                'plural' => 'Single ' . $language['name'],
                'single_vi' => 'Single ' . $language['name'],
                'plural_vi' => 'Single ' . $language['name'],
                'type' => 'text',
                'mandatory' => 1,
                'unique' => 0,
                'regex' => '',
                'error' => '',
                'unique' => 0,
                'minlength' => 0,
                'maxlength' => 0,
                'order' => $count,
                'level' => 15,
                'statistic' => 'name',
                'display' => 'name'
            ];
            $count++;
            $fields[] = [
                'table' => "$module",
                'field_note' => "",
                'id' => $count,
                'field' => 'plural' . $language['code'],
                'single' => 'Plural ' . $language['name'],
                'plural' => 'Plural ' . $language['name'],
                'single_vi' => 'Plural ' . $language['name'],
                'plural_vi' => 'Plural ' . $language['name'],
                'type' => 'text',
                'mandatory' => 1,
                'unique' => 0,
                'minlength' => 0,
                'maxlength' => 0,
                'regex' => '',
                'error' => '',
                'order' => $count,
                'level' => 14,
                'statistic' => 'name',
                'display' => 'name'
            ];
        }

        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'type',
            'single' => 'Type',
            'plural' => 'Types',
            'single_vi' => 'Type',
            'plural_vi' => 'Types',
            'type' => 'enum',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => '',
            'display' => '[
                "integer","float","double","sequence",
                "text","textarea","email","phone","link",
                "html","password","file",
                "enum","enums","level","checkbox",
                "date","datetime","eventstart","eventend",
                "self","belong","weakbelong","has","have"
            ]'
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'mandatory',
            'single' => 'Mandatory',
            'plural' => 'Mandatories',
            'single_vi' => 'Mandatory',
            'plural_vi' => 'Mandatories',
            'type' => 'checkbox',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'is_unique',
            'single' => 'Unique',
            'plural' => 'Unique',
            'single_vi' => 'Unique',
            'plural_vi' => 'Unique',
            'type' => 'checkbox',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'minlength',
            'single' => 'Min Length',
            'plural' => 'Min Lengths',
            'single_vi' => 'Min Length',
            'plural_vi' => 'Min Lengths',
            'type' => 'integer',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 14,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'maxlength',
            'single' => 'Max Length',
            'plural' => 'Max Lengths',
            'single_vi' => 'Max Length',
            'plural_vi' => 'Max Lengths',
            'type' => 'integer',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 14,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'regex',
            'single' => 'Regex',
            'plural' => 'Regexes',
            'single_vi' => 'Regex',
            'plural_vi' => 'Regexes',
            'type' => 'text',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 14,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'error',
            'single' => 'Error',
            'plural' => 'Errors',
            'single_vi' => 'Error',
            'plural_vi' => 'Errors',
            'type' => 'text',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 14,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'order_by',
            'single' => 'Order',
            'plural' => 'Orders',
            'single_vi' => 'Order',
            'plural_vi' => 'Orders',
            'type' => 'sequence',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'level',
            'single' => 'Level',
            'plural' => 'Levels',
            'single_vi' => 'Level',
            'plural_vi' => 'Levels',
            'type' => 'level',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => '',
            'display' => '{
                "List":1,
                "Show":2,
                "Add":4,
                "Edit":8
            }'
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'statistic',
            'single' => 'Statistic',
            'plural' => 'Statistics',
            'single_vi' => 'Statistic',
            'plural_vi' => 'Statistics',
            'type' => 'text',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 14,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'display',
            'single' => 'Display',
            'plural' => 'Displays',
            'single_vi' => 'Display',
            'plural_vi' => 'Displays',
            'type' => 'text',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 14,
            'statistic' => '',
            'display' => ''
        ];
        $count++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'note',
            'single' => 'Note',
            'plural' => 'Notes',
            'single_vi' => 'Note',
            'plural_vi' => 'Notes',
            'type' => 'textarea',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
            'order' => $count,
            'level' => 14,
            'statistic' => '',
            'display' => ''
        ];

        return $fields;
    }

    /**
     * @throws
     */
    public function create($user, $item, $many = null)
    {
        $result = $this->insert($item);

        $lzamodules = $this->mainDb->lzamodule(
            'id', $result["lzamodule_id"]
        );
        $lzamodule = $lzamodules->fetch();
        if (!$result)
        {
            return;
        }
        $this->updateManyToManyReference(
            $result['id'], $many
        );
        if (
            in_array(
                $result["type"],
                ['text', 'email', 'phone', 'link', 'file']
            )
        )
        {
            $newColumn = "{$result["field"]} varchar({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL DEFAULT ''";
        }

        if (in_array($result["type"], ['password']))
        {
            $length = 10 * $result["maxlength"];
            $newColumn = "{$result["field"]} varchar({$length}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL DEFAULT ''";
        }

        if (in_array($result["type"], ['textarea', 'html']))
        {
            $newColumn = "{$result["field"]} text ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL DEFAULT ''";
        }

        if (in_array($result["type"], ['date']))
        {
            $newColumn = "{$result["field"]} date ";
            $newColumn .= $result["mandatory"] === 0
                    ? 'DEFAULT NULL' : "NOT NULL DEFAULT '1900-01-01'";
        }

        if (in_array($result["type"], ['enum']))
        {
            $values = implode("','", json_decode($result["display"], true));
            $newColumn = "{$result["field"]} enum('{$values}') ";
            $newColumn .= $result["mandatory"] === 0
                    ? 'DEFAULT NULL' : "NOT NULL";
        }

        if (in_array($result["type"], ['datetime']))
        {
            $newColumn = "{$result["`field"]} datetime ";
            $newColumn .= $result["mandatory"] === 0
                    ? 'DEFAULT NULL' : "NOT NULL DEFAULT '1900-01-01 00:00:00'";
        }

        if (in_array($result["type"], ['checkbox']))
        {
            $newColumn = "{$result["field"]} tinyint(1) NOT NULL DEFAULT 0";
        }

        if (in_array($result["type"], ['belong']))
        {
            $newColumn = "{$result["field"]}_id int({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? 'DEFAULT NULL' : "NOT NULL";
        }

        if (in_array($result["type"], ['integer', 'self', 'weakbelong', 'sequence']))
        {
            $newColumn = "{$result["field"]} int({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL";
        }

        if (in_array($result["type"], ['float']))
        {
            $newColumn = "{$result["field"]} float({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL";
        }

        if (in_array($result["type"], ['double']))
        {
            $newColumn = "{$result["field"]} double({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL";
        }

        if (in_array($result["type"], ['have']))
        {
            $localTable = "{$lzamodule['name']}";
            $foreignTable = str_replace([$lzamodule['name'], '_'], ['', ''], $result["field"]);
            DatabasePool::getConnection()->exec("
                CREATE TABLE IF NOT EXISTS {$result["field"]}
                (
                    {$localTable}_id int(11) unsigned NOT NULL,
                    {$foreignTable}_id int(11) unsigned NOT NULL,
                    KEY ref_{$result["field"]}_{$localTable}_idx ({$localTable}_id),
                    CONSTRAINT {$result["field"]}_{$localTable}
                        FOREIGN KEY ({$localTable}_id)
                        REFERENCES {$localTable} (id),
                    KEY ref_{$result["field"]}_{$foreignTable}_idx ({$foreignTable}_id),
                    CONSTRAINT {$result["field"]}_{$foreignTable}
                        FOREIGN KEY ({$foreignTable}_id)
                        REFERENCES {$foreignTable} (id)
                )
                ENGINE=InnoDB
                DEFAULT CHARSET=utf8
            ");
            return $result;
        }
        DatabasePool::getConnection()->exec(
            "call add_column_if_not_exists(
                \"{$lzamodule['name']}\",
                \"{$newColumn}\"
            )"
        );
        DatabasePool::getConnection()->exec(
            "call add_column_if_not_exists(
                \"{$lzamodule['name']}_history\",
                \"{$newColumn}\"
            )"
        );
        return $result;
    }

    /**
     * @throws
     */
    public function modify($user, $original, $changed, $many = null)
    {
        $result = count($changed) > 0
                ? $original->update($changed) : null;
        if (
            $result === null &&
            $this->updateManyToManyReference(
                $original['id'], $many
            )
        )
        {
            return $original;
        }
        elseif ($result === null && count($changed) === 0)
        {
            return $original;
        }
        elseif ($result)
        {
            $this->updateManyToManyReference(
                $original['id'], $many
            );
        }

        $lzamodules = $this->mainDb->lzamodule(
            'id', $result["lzamodule_id"]
        );
        $lzamodule = $lzamodules->fetch();
        if (
            in_array(
                $original['type'],
                ['text', 'email', 'phone', 'link', 'file']
            )
        )
        {
            $newColumn = "{$result["field"]} varchar({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL DEFAULT ''";
        }

        if (in_array($original['type'], ['password']))
        {
            $length = 10 * $result["maxlength"];
            $newColumn = "{$result["field"]} varchar({$length}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL DEFAULT ''";
        }

        if (in_array($original['type'], ['textarea', 'html']))
        {
            $newColumn = "{$result["field"]} text ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL DEFAULT ''";
        }

        if (in_array($original['type'], ['date']))
        {
            $newColumn = "{$result["field"]} date ";
            $newColumn .= $result["mandatory"] === 0
                    ? 'DEFAULT NULL'
                    : "NOT NULL DEFAULT '1900-01-01'";
        }

        if (in_array($original['type'], ['enum']))
        {
            $values = implode("','", json_decode($result["display"], true));
            $newColumn = "{$result["field"]} enum('{$values}') ";
            $newColumn .= $result["mandatory"] === 0
                    ? 'DEFAULT NULL' : "NOT NULL";
        }

        if (in_array($original['type'], ['datetime']))
        {
            $newColumn = "{$result["field"]} datetime ";
            $newColumn .= $result["mandatory"] === 0
                    ? 'DEFAULT NULL'
                    : "NOT NULL DEFAULT '1900-01-01 00:00:00'";
        }

        if (in_array($original['type'], ['checkbox']))
        {
            $newColumn = "{$result["field"]} tinyint(1) NOT NULL DEFAULT 0";
        }

        if (in_array($original['type'], ['belong']))
        {
            $newColumn = "`{$result["field"]}_id` int({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? 'DEFAULT NULL' : "NOT NULL";
        }

        if (
            in_array(
                $original['type'],
                ['integer', 'self', 'weakbelong', 'sequence']
            )
        )
        {
            $newColumn = "{$result["field"]} int({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL";
        }

        if (in_array($original['type'], ['float']))
        {
            $newColumn = "{$result["field"]} float({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL";
        }

        if (in_array($original['type'], ['double']))
        {
            $newColumn = "{$result["field"]} double({$result["maxlength"]}) ";
            $newColumn .= $result["mandatory"] === 0
                    ? '' : "NOT NULL";
        }

        if (in_array($result["type"], ['have']))
        {
            $localTable = "{$lzamodule['name']}";
            $foreignTable = str_replace(
                [$lzamodule['name'], '_'], ['', ''],
                $result["field"]
            );
            DatabasePool::getConnection()->exec("
                CREATE TABLE IF NOT EXISTS {$result["field"]}
                (
                    {$localTable}_id int(11) unsigned NOT NULL,
                    {$foreignTable}_id int(11) unsigned NOT NULL,
                    KEY ref_{$result["field"]}_{$localTable}_idx ({$localTable}_id),
                    CONSTRAINT {$result["field"]}_{$localTable}
                        FOREIGN KEY ({$localTable}_id)
                        REFERENCES {$localTable} (id),
                    KEY ref_{$result["field"]}_{$foreignTable}_idx ({$foreignTable}_id),
                    CONSTRAINT {$result["field"]}_{$foreignTable}
                        FOREIGN KEY ({$foreignTable}_id)
                        REFERENCES {$foreignTable} (id)
                )
                ENGINE=InnoDB
                DEFAULT CHARSET=utf8"
            );
            return $result;
        }
        DatabasePool::getConnection()->exec(
            "call modify_column_if_exists(
                '{$original->lzamodule['name']}',
                '{$newColumn}'
            )"
        );
        DatabasePool::getConnection()->exec(
            "call modify_column_if_exists(
                '{$original->lzamodule['name']}_history',
                '{$newColumn}'
            )"
        );
        return $result;
    }

    /**
     * @throws
     */
    protected function updateManyToManyReference($id, $many = null)
    {
        if ($many === null || count($many) === 0)
        {
            return false;
        }
        $tableModuleName = snake_case($this->module);
        $tableIdModule = snake_case($this->module) . '_id';

        $result = false;
        foreach ($many as $table => $manyItems)
        {
            $result = true;
            $tableIdRefence = trim(
                str_replace(
                    $tableModuleName, '',
                    str_replace(
                        ["`", "`"], '',
                        $table
                    )
                ),
                '_'
            ) . '_id';
            $insertingItems = [];
            foreach ($manyItems as $manyItem)
            {
                $insertingItems[] = [
                    $tableIdModule => intval($id),
                    $tableIdRefence => intval($manyItem)
                ];
            }

            $this->mainDb->$table($tableIdModule, $id)->delete();
            $this->mainDb->$table()->insert_multi($insertingItems);
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
            foreach ($this->tableFields as $field)
            {
                if (strcmp($field['type'], 'have') === 0)
                {
                    $many[$field['field']] = [];
                }
            }

            $this->updateManyToManyReference($id, $many);
            if ($this->where("id", $id)->delete())
            {
                if ($original['unique'] === 1)
                {
                    DatabasePool::getConnection()->exec("
                        call drop_index_if_exists(
                            '{$original->lzamodule['name']}',
                            '{$original->lzamodule['name']}_{$original['field']}'
                        )
                    ");
                }
                if (in_array($original['type'], ['belong']) === 0)
                {
                    DatabasePool::getConnection()->exec("
                        call drop_index_if_exists(
                            '{$original->lzamodule['name']}',
                            '{$original['field']}_{$original->lzamodule['name']}'
                        )
                    ");
                }
                DatabasePool::getConnection()->exec("
                    call drop_column_if_exists(
                        '{$original->lzamodule['name']}',
                        '{$original['field']}'
                    )
                ");
                DatabasePool::getConnection()->exec("
                    call drop_column_if_exists(
                        '{$original->lzamodule['name']}_history',
                        '{$original['field']}'
                    )
                ");
                return true;
            }
        }
        return false;
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
}
