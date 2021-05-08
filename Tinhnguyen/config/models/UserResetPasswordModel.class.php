<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * User Reset Password Model
 * Access to user_reset_password table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserResetPasswordModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'user_reset_password';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'id' => 0,
            'db_id' => 'main',
            'name' => 'user_reset_password',
            'single' => 'User Reset Password',
            'plural' => 'User Reset Passwords',
            'single_vi' => 'User Reset Password',
            'plural_vi' => 'User Reset Passwords',
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
            'field' => 'email',
            'single' => 'Email',
            'plural' => 'Emails',
            'single_vi' => 'Email',
            'plural_vi' => 'Emails',
            'type' => 'email',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 200,
            'regex' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'token',
            'single' => 'Token',
            'plural' => 'Tokens',
            'single_vi' => 'Token',
            'plural_vi' => 'Tokens',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 200,
            'regex' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'expire',
            'single' => 'Expiry Date',
            'plural' => 'Expiry Dates',
            'single_vi' => 'Expiry Date',
            'plural_vi' => 'Expiry Dates',
            'type' => 'datetime',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'order' => $count,
            'level' => 3,
            'statistic' => 'email',
            'display' => ''
        ];
        
        return $fields;
    }
}
