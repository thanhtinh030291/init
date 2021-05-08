<?php

namespace Lza\Config\Models;


use Lza\Config\Models\LzaModel;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Test Model
 * Access to test table
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class TestModel extends LzaModel
{
    use Singleton;

    /**
     * @var string Database Table
     */
    protected $module = 'test';

    /**
     * @throws
     */
    public function getTable($module = null)
    {
        return [
            'id' => -100,
            'db_id' => 'main',
            'name' => 'test',
            'single' => 'Test',
            'plural' => 'Tests',
            'single_vi' => 'Test',
            'plural_vi' => 'Tests',
            'display' => 'textfield',
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
            'field' => 'textfield',
            'single' => 'Text',
            'plural' => 'Texts',
            'single_vi' => 'Texta',
            'plural_vi' => 'Texts',
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
            'field' => 'passwordfield',
            'single' => 'Password',
            'plural' => 'Passwords',
            'single_vi' => 'Password',
            'plural_vi' => 'Passwords',
            'type' => 'password',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 12,
            'statistic' => '',
            'display' => ''
        ];
        $count ++;
        
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'emailfield',
            'single' => 'Email',
            'plural' => 'Emails',
            'single_vi' => 'Email',
            'plural_vi' => 'Emails',
            'type' => 'email',
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
            'field' => 'phonefield',
            'single' => 'Phone',
            'plural' => 'Phones',
            'single_vi' => 'Phone',
            'plural_vi' => 'Phones',
            'type' => 'phone',
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
            'field' => 'linkfield',
            'single' => 'Link',
            'plural' => 'Links',
            'single_vi' => 'Link',
            'plural_vi' => 'Links',
            'type' => 'link',
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
            'field' => 'filefield',
            'single' => 'File',
            'plural' => 'Files',
            'single_vi' => 'File',
            'plural_vi' => 'File',
            'type' => 'file',
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
            'field' => 'textareafield',
            'single' => 'TextArea',
            'plural' => 'TextAreas',
            'single_vi' => 'TextArea',
            'plural_vi' => 'TextAreas',
            'type' => 'textarea',
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
            'field' => 'htmlfield',
            'single' => 'Html',
            'plural' => 'Htmls',
            'single_vi' => 'Html',
            'plural_vi' => 'Htmls',
            'type' => 'html',
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
            'field' => 'jsonfield',
            'single' => 'Json',
            'plural' => 'Jsons',
            'single_vi' => 'Json',
            'plural_vi' => 'Jsons',
            'type' => 'json',
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
            'field' => 'checkboxfield',
            'single' => 'CheckBox',
            'plural' => 'CheckBoxes',
            'single_vi' => 'CheckBox',
            'plural_vi' => 'CheckBoxes',
            'type' => 'checkbox',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'textfield',
            'display' => 'textfield'
        ];
        $count ++;
        
        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'integerfield',
            'single' => 'Integer',
            'plural' => 'Integers',
            'single_vi' => 'Integer',
            'plural_vi' => 'Integers',
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
        $count ++;

        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'sequencefield',
            'single' => 'Sequence',
            'plural' => 'Sequences',
            'single_vi' => 'Sequence',
            'plural_vi' => 'Sequences',
            'type' => 'sequence',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'textfield',
            'display' => 'textfield'
        ];
        $count ++;

        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'floatfield',
            'single' => 'Float',
            'plural' => 'Floats',
            'single_vi' => 'Float',
            'plural_vi' => 'Floats',
            'type' => 'float',
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
            'field' => 'doublefield',
            'single' => 'Double',
            'plural' => 'Doubles',
            'single_vi' => 'Double',
            'plural_vi' => 'Doubles',
            'type' => 'double',
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
            'field' => 'datefield',
            'single' => 'Date',
            'plural' => 'Dates',
            'single_vi' => 'Date',
            'plural_vi' => 'Dates',
            'type' => 'date',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'textfield',
            'display' => 'textfield'
        ];
        $count ++;

        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'datetimefield',
            'single' => 'DateTime',
            'plural' => 'DateTimes',
            'single_vi' => 'DateTime',
            'plural_vi' => 'DateTimes',
            'type' => 'datetime',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'textfield',
            'display' => 'textfield'
        ];
        $count ++;

        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'eventstartfield',
            'single' => 'EventStart',
            'plural' => 'EventStarts',
            'single_vi' => 'EventStart',
            'plural_vi' => 'EventStarts',
            'type' => 'eventstart',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'textfield',
            'display' => 'textfield'
        ];
        $count ++;

        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'eventendfield',
            'single' => 'EventEnd',
            'plural' => 'EventEnds',
            'single_vi' => 'EventEnd',
            'plural_vi' => 'EventEnds',
            'type' => 'eventend',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'textfield',
            'display' => 'textfield'
        ];
        $count ++;

        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'enumfield',
            'single' => 'Enum',
            'plural' => 'Enums',
            'single_vi' => 'Enum',
            'plural_vi' => 'Enums',
            'type' => 'enum',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'textfield',
            'display' => '["test1","test2"]'
        ];
        $count ++;

        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'enumsfield',
            'single' => 'Enums',
            'plural' => 'Enums',
            'single_vi' => 'Enums',
            'plural_vi' => 'Enums',
            'type' => 'enums',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'textfield',
            'display' => '["test1","test2","test3"]'
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
            'plural_vi' => 'Parent',
            'type' => 'self',
            'mandatory' => 0,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => 'textfield',
            'display' => 'textfield'
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
            'field' => 'test_provider',
            'single' => 'Provider',
            'plural' => 'Providers',
            'single_vi' => 'Provider',
            'plural_vi' => 'Providers',
            'type' => 'have',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 14,
            'statistic' => 'name',
            'display' => 'name'
        ];
        $count ++;

        $fields[] = [
            'table' => "$module",
            'field_note' => "",
            'id' => $count,
            'field' => 'levelfield',
            'single' => 'Level',
            'plural' => 'Levels',
            'single_vi' => 'Level',
            'plural_vi' => 'Levels',
            'type' => 'level',
            'mandatory' => 1,
            'unique' => 0,
            'minlength' => 0,
            'maxlength' => 0,
            'order' => $count,
            'level' => 15,
            'statistic' => '',
            'display' => '{
                "Test1":1,
                "Test2":2,
                "Test3":4
            }'
        ];
        $count ++;
        
        return $fields;
    }
}
