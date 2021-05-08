<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Lza Permission Model
 * Access to lzapermission table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LzapermissionModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'lzapermission';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'db_id' => 'main',
            'id' => 'lzapermission',
            'single' => 'Permission',
            'plural' => 'Permissions',
            'single_vi' => 'Permission',
            'plural_vi' => 'Permissions',
            'display' => 'id',
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
            'field' => 'lzarole',
            'single' => 'Role',
            'plural' => 'Roles',
            'single_vi' => 'Role',
            'plural_vi' => 'Roles',
            'type' => 'belong',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'error' => '',
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

        return $fields;
    }
}
