<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Lza Notification Model
 * Access to lzanotification table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LzanotificationModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'lzanotification';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'db_id' => 'main',
            'id' => 'lzanotification',
            'single' => 'Notification',
            'plural' => 'Notifications',
            'single_vi' => 'Notification',
            'plural_vi' => 'Notifications',
            'display' => 'subject',
            'enabled' => 'Yes',
            'icon' => 'envelope',
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
            'field' => 'time',
            'single' => 'Time',
            'plural' => 'Times',
            'single_vi' => 'Time',
            'plural_vi' => 'Times',
            'type' => 'datetime',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 3,
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
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
            'order' => $count,
            'level' => 15,
            'statistic' => '',
            'display' => '["device","topic","group"]'
        ];
        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'receivers',
            'single' => 'Receivers',
            'plural' => 'Receivers',
            'single_vi' => 'Receivers',
            'plural_vi' => 'Receivers',
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
            'field' => 'subject',
            'single' => 'Subject',
            'plural' => 'Subjects',
            'single_vi' => 'Subject',
            'plural_vi' => 'Subjects',
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
            'field' => 'message',
            'single' => 'Message',
            'plural' => 'Messages',
            'single_vi' => 'Message',
            'plural_vi' => 'Messages',
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
            'field' => 'data',
            'single' => 'Data',
            'plural' => 'Data',
            'single_vi' => 'Data',
            'plural_vi' => 'Data',
            'type' => 'text',
            'mandatory' => 0,
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
            'field' => 'icon',
            'single' => 'Icon',
            'plural' => 'Icons',
            'single_vi' => 'Icon',
            'plural_vi' => 'Icons',
            'type' => 'text',
            'mandatory' => 0,
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
            'field' => 'color',
            'single' => 'Color',
            'plural' => 'Colors',
            'single_vi' => 'Color',
            'plural_vi' => 'Colors',
            'type' => 'text',
            'mandatory' => 0,
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
            'field' => 'badge',
            'single' => 'Badge',
            'plural' => 'Badges',
            'single_vi' => 'Badge',
            'plural_vi' => 'Badges',
            'type' => 'text',
            'mandatory' => 0,
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
            'field' => 'try',
            'single' => 'Try',
            'plural' => 'Tries',
            'single_vi' => 'Try',
            'plural_vi' => 'Tries',
            'type' => 'integer',
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
