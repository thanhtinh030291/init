<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Lza Setting Model
 * Access to lzasetting table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class LzasettingModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'lzasetting';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'db_id' => 'main',
            'id' => 'lzasetting',
            'single' => 'Setting',
            'plural' => 'Settings',
            'single_vi' => 'Setting',
            'plural_vi' => 'Settings',
            'display' => 'id',
            'enabled' => 'Yes',
            'icon' => 'gears',
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
            'field' => 'lzasection',
            'single' => 'Section',
            'plural' => 'Sections',
            'single_vi' => 'Section',
            'plural_vi' => 'Sections',
            'type' => 'belong',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'title',
            'display' => 'title'
        ];

        foreach ($this->mainDb->lzalanguage() as $language)
        {
            $count++;
            $fields[] = [
                'table' => "$module",
                'field_note' => "",
                'id' => $count,
                'field' => 'title' . $language['code'],
                'single' => 'Title' . $language['name'],
                'plural' => 'Title' . $language['name'],
                'single_vi' => 'Title' . $language['name'],
                'plural_vi' => 'Title' . $language['name'],
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
        }

        $count ++;
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'value',
            'single' => 'Value',
            'plural' => 'Values',
            'single_vi' => 'Value',
            'plural_vi' => 'Values',
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
            'display' => '[
                "number",
                "text","textarea","email","phone","link",
                "html","password","file",
                "enum","checkbox",
                "date","datetime","eventstart","eventend"
            ]'
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
            'type' => 'text',
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
