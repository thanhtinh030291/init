<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Lza User Model
 * Access to lzauser table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LzauserModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'lzauser';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'id' => -16,
            'db_id' => 'main',
            'name' => 'user',
            'single' => 'User',
            'plural' => 'Users',
            'single_vi' => 'User',
            'plural_vi' => 'Users',
            'display' => 'fullname',
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
            'field' => 'username',
            'single' => 'Username',
            'plural' => 'Usernames',
            'single_vi' => 'Username',
            'plural_vi' => 'Usernames',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 50,
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
            'field' => 'password',
            'single' => 'Password',
            'plural' => 'Passwords',
            'single_vi' => 'Password',
            'plural_vi' => 'Passwords',
            'type' => 'password',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 50,
            'regex' => '',
            'order' => $count,
            'error' => 'invalid_password',
            'level' => 12,
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'fullname',
            'single' => 'Full Name',
            'plural' => 'Full Names',
            'single_vi' => 'Full Name',
            'plural_vi' => 'Full Names',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 50,
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
            'order' => $count,
            'level' => 15,
            'statistic' => 'name',
            'display' => 'name'
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'is_admin',
            'single' => 'Is Admin',
            'plural' => 'Is Admin',
            'single_vi' => 'Is Admin',
            'plural_vi' => 'Is Admin',
            'type' => 'enum',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => 'fullname',
            'display' => '["Yes","No"]'
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'provider',
            'single' => 'Provider',
            'plural' => 'Providers',
            'single_vi' => 'Provider',
            'plural_vi' => 'Providers',
            'type' => 'belong',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'order' => $count,
            'level' => 15,
            'statistic' => 'name',
            'display' => 'name'
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'notify',
            'single' => 'Notify',
            'plural' => 'Notifies',
            'single_vi' => 'Notify',
            'plural_vi' => 'Notifies',
            'type' => 'checkbox',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 1,
            'order' => $count,
            'regex' => '',
            'level' => 14,
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
            'type' => 'checkbox',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 1,
            'regex' => '',
            'order' => $count,
            'level' => 14,
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'expiry',
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
            'statistic' => 'fullname',
            'display' => ''
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'last_reset_by',
            'single' => 'Last Reset By',
            'plural' => 'Last Reset By',
            'single_vi' => 'Last Reset By',
            'plural_vi' => 'Last Reset By',
            'type' => 'text',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
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
            'field' => 'last_reset_at',
            'single' => 'Last Reset At',
            'plural' => 'Last Reset At',
            'single_vi' => 'Last Reset At',
            'plural_vi' => 'Last Reset At',
            'type' => 'datetime',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'regex' => '',
            'order' => $count,
            'level' => 3,
            'statistic' => 'fullname',
            'display' => ''
        ];

        return $fields;
    }

    /**
     * @throws
     */
    public function login($usernameOrEmail, $password)
    {
        $users = $this->where(
            "(username = ? or email = ?) and password = ? and enabled = 1",
            $usernameOrEmail, $usernameOrEmail, $password
        );
        return $users->fetch();
    }

    /**
     * @throws
     */
    public function getUserByEmailAndPassword($email, $password)
    {
        $users = $this->where("email = ? and password = ?", $email, $password);
        return $users->fetch();
    }

    /**
     * @throws
     */
    public function getUserByEmail($email)
    {
        return $this->where("email", $email)->fetch();
    }
}
