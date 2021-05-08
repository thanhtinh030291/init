<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Lza Task Model
 * Access to lzatask table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LzataskModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'lzatask';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'id' => -14,
            'db_id' => 'main',
            'name' => 'lzatask',
            'single' => 'Task',
            'plural' => 'Tasks',
            'single_vi' => 'Task',
            'plural_vi' => 'Tasks',
            'display' => 'name',
            'enabled' => 'Yes',
            'icon' => 'tasks',
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
            'maxlength' => 255,
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
            'field' => 'minute',
            'single' => 'Minute(s)',
            'plural' => 'Minute(s)',
            'single_vi' => 'Minute(s)',
            'plural_vi' => 'Minutes(s)',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 255,
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
            'field' => 'hour',
            'single' => 'Hour(s)',
            'plural' => 'Hour(s)',
            'single_vi' => 'Hour(s)',
            'plural_vi' => 'Hour(s)',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 255,
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
            'field' => 'week_day',
            'single' => 'Day(s) of Week',
            'plural' => 'Day(s) of Week',
            'single_vi' => 'Day(s) of Week',
            'plural_vi' => 'Day(s) of Week',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 255,
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
            'field' => 'month_day',
            'single' => 'Day(s) of Month',
            'plural' => 'Day(s) of Month',
            'single_vi' => 'Day(s) of Month',
            'plural_vi' => 'Day(s) of Month',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 255,
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
            'field' => 'month',
            'single' => 'Month(s)',
            'plural' => 'Month(s)',
            'single_vi' => 'Month(s)',
            'plural_vi' => 'Month(s)',
            'type' => 'text',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 255,
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
            'field' => 'class',
            'single' => 'Class',
            'plural' => 'Classes',
            'single_vi' => 'Class',
            'plural_vi' => 'Classes',
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
            'field' => 'enabled',
            'single' => 'Enabled',
            'plural' => 'Enabled',
            'single_vi' => 'Enabled',
            'plural_vi' => 'Enabled',
            'type' => 'checkbox',
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
