<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Lza Statistic Model
 * Access to lzastatistic table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LzastatisticModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'lzastatistic';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'db_id' => 'main',
            'id' => 'lzastatistic',
            'single' => 'Statistic',
            'plural' => 'Statistics',
            'single_vi' => 'Statistic',
            'plural_vi' => 'Statistics',
            'display' => 'name',
            'enabled' => 'Yes',
            'icon' => 'bar-chart-o',
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
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'user',
            'single' => 'User',
            'plural' => 'Users',
            'single_vi' => 'User',
            'plural_vi' => 'Users',
            'type' => 'belong',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'fullname',
            'display' => 'fullname'
        ];
        $count ++;
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
            'order' => $count,
            'level' => 15,
            'statistic' => 'single',
            'display' => 'single'
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
            'type' => 'belong',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'single',
            'display' => 'single'
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'conditions',
            'single' => 'Condtions',
            'plural' => 'Conditions',
            'single_vi' => 'Condtions',
            'plural_vi' => 'Conditions',
            'type' => 'textarea',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
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
            'field' => 'extra',
            'single' => 'Extra',
            'plural' => 'Extras',
            'single_vi' => 'Extra',
            'plural_vi' => 'Extras',
            'type' => 'textarea',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
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
                "Pie Chart",
                "Horizontal Bar Chart",
                "Vertical Bar Chart",
                "Yearly Line Chart",
                "Quarterly Line Chart",
                "Monthly Line Chart",
                "Weekly Line Chart",
                "Daily Line Chart",
                "Yearly Area Chart",
                "Quarterly Area Chart",
                "Monthly Area Chart",
                "Weekly Area Chart",
                "Daily Area Chart"
            ]'
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'width',
            'single' => 'Width',
            'plural' => 'Widths',
            'single_vi' => 'Width',
            'plural_vi' => 'Widths',
            'type' => 'enum',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 14,
            'statistic' => '',
            'display' => '[
                "6","7","8","9",
                "10","11","12"
            ]'
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
            'statistic' => '',
            'display' => ''
        ];

        return $fields;
    }
}
