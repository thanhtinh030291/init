<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * User Module Model
 * Access to user_module table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserModuleModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'user_module';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'id' => 0,
            'db_id' => 'main',
            'name' => 'user_module',
            'single' => 'User Module',
            'plural' => 'User Modules',
            'single_vi' => 'User Module',
            'plural_vi' => 'User Modules',
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
            'type' => 'integer',
            'mandatory' => 0,
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
            'field' => 'icon',
            'single' => 'Icon',
            'plural' => 'Icons',
            'single_vi' => 'Icon',
            'plural_vi' => 'Icons',
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
                'level' => 2,
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
                'level' => 2,
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
            'level' => 2,
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
            'level' => 2,
            'regex' => '',
            'statistic' => '',
            'display' => ''
        ];
        
        return $fields;
    }
}
