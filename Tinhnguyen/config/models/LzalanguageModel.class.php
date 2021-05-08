<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Lza Language Model
 * Access to lzalanguage table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LzalanguageModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'lzalanguage';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'db_id' => 'main',
            'id' => 'lzalanguage',
            'single' => 'Language',
            'plural' => 'Languages',
            'single_vi' => 'Language',
            'plural_vi' => 'Languages',
            'display' => 'name',
            'enabled' => 'Yes',
            'icon' => 'language',
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
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
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
            'field' => 'name',
            'single' => 'Name',
            'plural' => 'Names',
            'single_vi' => 'Name',
            'plural_vi' => 'Names',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
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
            'field' => 'code',
            'single' => 'Code',
            'plural' => 'Codes',
            'single_vi' => 'Code',
            'plural_vi' => 'Codes',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
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
            'statistic' => '',
            'display' => ''
        ];

        return $fields;
    }
}
