<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * User Permission Model
 * Access to user_permission view
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserPermissionModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'user_permission';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'id' => 0,
            'db_id' => 'main',
            'name' => 'user_permission',
            'single' => 'User Permission',
            'plural' => 'User Permissions',
            'single_vi' => 'User Permission',
            'plural_vi' => 'User Permissions',
            'display' => 'id',
            'enabled' => 'Yes',
            'icon' => 'user',
            'sort' => '[1,"asc"]',
            'settings' => ''
        ];
    }

    /**
     * @throws
     */
    public function getTableFields($module = null, $conditions = [])
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
            'field' => 'module_id',
            'single' => 'Module Id',
            'plural' => 'Module Ids',
            'single_vi' => 'Module Id',
            'plural_vi' => 'Module Ids',
            'type' => 'integer',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 2,
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'username',
            'single' => 'Username',
            'plural' => 'Usernames',
            'single_vi' => 'Username',
            'plural_vi' => 'Usernames',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 200,
            'regex' => '',
            'order' => $count,
            'level' => 2,
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'level',
            'single' => 'Level',
            'plural' => 'Levels',
            'single_vi' => 'Level',
            'plural_vi' => 'Levels',
            'type' => 'integer',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 200,
            'regex' => '',
            'order' => $count,
            'level' => 2,
            'statistic' => '',
            'display' => ''
        ];
        
        return $fields;
    }
}
