<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Lza Module Model
 * Access to lzamodule table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LzamoduleModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'lzamodule';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'db_id' => 'main',
            'id' => 'lzamodule',
            'single' => 'Module',
            'plural' => 'Modules',
            'single_vi' => 'Module',
            'plural_vi' => 'Modules',
            'display' => 'single',
            'enabled' => 'Yes',
            'icon' => 'table',
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
        $count ++;
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
            'order' => $count,
            'level' => 2,
            'regex' => '',
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'parent',
            'single' => 'Parent',
            'plural' => 'Parents',
            'single_vi' => 'Parent',
            'plural_vi' => 'Parents',
            'type' => 'self',
            'regex' => '',
            'mandatory' => 1,
            'unique' => 1,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 7,
            'regex' => '',
            'statistic' => '',
            'display' => 'id'
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'icon',
            'single' => 'Icon',
            'plural' => 'Icons',
            'single_vi' => 'Icon',
            'plural_vi' => 'Icons',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'regex' => '',
            'statistic' => '',
            'display' => ''
        ];

        foreach ($this->mainDb->lzalanguage() as $language)
        {
            $count ++;
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
                'minlength' => 0,
                'maxlength' => 0,
                'order' => $count,
                'level' => 14,
                'regex' => '',
                'statistic' => 'name',
                'display' => 'name'
            ];
            $count ++;
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
                'order' => $count,
                'level' => 14,
                'regex' => '',
                'statistic' => 'name',
                'display' => 'name'
            ];
        }

        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'note',
            'single' => 'Note',
            'plural' => 'Notes',
            'single_vi' => 'Note',
            'plural_vi' => 'Notes',
            'type' => 'text',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 14,
            'regex' => '',
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
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
            'order' => $count,
            'level' => 14,
            'regex' => '',
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'sort',
            'single' => 'Sort',
            'plural' => 'Sorts',
            'single_vi' => 'Sort',
            'plural_vi' => 'Sorts',
            'type' => 'json',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 14,
            'regex' => '',
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'enabled',
            'single' => 'Enabled',
            'plural' => 'Enabled',
            'single_vi' => 'Enabled',
            'plural_vi' => 'Enabled',
            'type' => 'enum',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'regex' => '',
            'statistic' => '',
            'display' => '["Yes","No"]'
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'settings',
            'single' => 'Settings',
            'plural' => 'Settings',
            'single_vi' => 'Settings',
            'plural_vi' => 'Settings',
            'type' => 'json',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 14,
            'regex' => '',
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'lzafield',
            'single' => 'Field',
            'plural' => 'Fields',
            'single_vi' => 'Field',
            'plural_vi' => 'Fields',
            'type' => 'has',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 2,
            'regex' => '',
            'statistic' => 'name',
            'display' => 'field'
        ];
        $count ++;
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
            'order' => $count,
            'level' => 15,
            'regex' => '',
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

        DatabasePool::getConnection()->exec("
            CREATE TABLE IF NOT EXISTS {$item["name"]}
            (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (id)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
        DatabasePool::getConnection()->exec("
            CREATE TABLE IF NOT EXISTS {$item["name"]}_history
            (
                action enum('Created','Updated','Deleted') DEFAULT 'Created',
                revision int(6) NOT NULL AUTO_INCREMENT,
                valid_from timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                id int(11) unsigned NOT NULL,
                PRIMARY KEY (id,revision)
            )
            ENGINE=MyISAM
            DEFAULT CHARSET=utf8
        ");

        if ($result)
        {
            $this->mainDb->lzafield()->insert([
                "lzamodule_id" => $result['id'],
                "field" => 'id',
                "single" => 'Id',
                "plural" => 'Ids',
                "single_vi" => 'Id',
                "plural_vi" => 'Ids',
                "type" => 'integer',
                "mandatory" => '1',
                "is_unique" => '1',
                "minlength" => '0',
                "maxlength" => '0',
                "regex" => '',
                "error" => '',
                "order" => '1',
                "level" => '2',
                "statistic" => '',
                "display" => '',
                "note" => 'Idfield number of the current item'
            ]);
            $this->updateManyToManyReference(
                $result['id'], $many
            );
        }
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
        return $result;
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
                DatabasePool::getConnection()->exec("
                    DROP TABLE IF EXISTS {$original['name']}
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
