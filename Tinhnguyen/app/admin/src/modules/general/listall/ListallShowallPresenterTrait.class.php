<?php

namespace Lza\App\Admin\Modules\General\Listall;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait ListallShowallPresenterTrait
{
    /**
     * @var array List of columns which reference to the same table
     */
    protected $selfColumns = [];

    /**
     * @var array List of columns which reference to the same table of another table
     */
    protected $belongSelfColumns = [];

    /**
     * @var array List of columns which reference to another table
     */
    protected $belongColumns = [];

    /**
     * @var array List of checkbox columns
     */
    protected $checkboxColumns = [];

    /**
     * @var array List of columns which reference to table with unformatted id style
     */
    protected $weakBelongColumns = [];

    /**
     * @var array List of enum columns
     */
    protected $enumColumns = [];

    /**
     * @var array List of enum set columns
     */
    protected $enumsColumns = [];

    /**
     * @var array List of level columns
     */
    protected $levelColumns = [];

    /**
     * @var array List of date columns
     */
    protected $dateColumns = [];

    /**
     * @var array List of datetime columns
     */
    protected $datetimeColumns = [];

    /**
     * @var array List of number columns
     */
    protected $numberColumns = [];

    /**
     * Validate inputs and do Get All Records request
     *
     * @throws
     */
    public function doShowAll($request, $regionPath, $tableInfo)
    {
        $region = $this->env->region;
        $module = $this->env->module;

        $table = snake_case($module);
        $tableUrl = chain_case($module);
        $modulePath = str_replace(
            '//', '/',
            chain_case($region) . "/{$tableUrl}"
        );

        $settings = $tableInfo['settings'];
        $this->options = empty($settings) ? [] : $this->encryptor->jsonDecode($settings);

        $this->getTableFields($table);

        $model = ModelPool::getModel($table);
        $dbInfo = $model->getDatabaseInfo();

        $columns = $this->getColumns($table);

        $filter = [$this->getFilter($request, $columns, $dbInfo)];

        $total = $model->select('count(0) as `total`')->fetch();
        $filteredTotal = call_user_func_array(
            [$model, 'where'],
            array_merge($filter, $this->filterParameters)
        );
        $filteredTotal = $filteredTotal->select('count(0) as `total`')->fetch();

        $data = call_user_func_array(
            [$model, 'where'],
            array_merge($filter, $this->filterParameters)
        );
        $data->select($this->getSelection($columns, $regionPath, $dbInfo));
        $data->order($this->getOrderBy($request, $columns));
        $data->limit($request->length, $request->start);
        $this->logger->log(LogLevel::DEBUG, "SQL: " . (string) $data);

        $parentFields = [];
        foreach ($this->fields as $key => $field)
        {
            if ($field['type'] === 'self')
            {
                $parentFields[$field['field']] = $field['display'];
            }
        }
        $weakBelongFields = [];
        foreach ($this->fields as $key => $field)
        {
            if ($field['type'] === 'weakbelong')
            {
                $weakBelongFields[$field['field']] = $field['display'];
            }
        }

        $items = [];
        foreach ($data as $dataKey => $dataValue)
        {
            if ($dbInfo['database_info']['type'] === 'oracle')
            {
                unset($dataValue['ORACLE_ROW_NUM']);
            }

            $item = [];
            foreach ($dataValue as $key => $value)
            {
                if (array_key_exists($key, $parentFields))
                {
                    if (isset($value))
                    {
                        $parents = $model->where(
                            "id", $value)->select($parentFields[$key]
                        );
                        $parent = $parents->fetch();
                        $item[] = "
                            <a style=\"color: #333\"
                               href=\"" . WEBSITE_ROOT . "{$modulePath}/show/{$value}\">
                                <i class=\"fa fa-navicon\"></i>
                                {$parent[$parentFields[$key]]}
                            </a>";
                    }
                    else
                    {
                        $item[] = '';
                    }
                }
                elseif (array_key_exists($key, $this->belongSelfColumns))
                {
                    if (isset($value))
                    {
                        $keyParts = explode('_', $key);
                        $belongParentTable = $keyParts[1];
                        $belongParentModel = ModelPool::getModel($belongParentTable);
                        $belongParents = $belongParentModel->where("id", $value);
                        $belongParents->select($this->belongSelfColumns[$key]);
                        $belongParent = $belongParents->fetch();
                        $belongParentTable = str_replace('_', '-', $belongParentTable);
                        $item[] = "
                            <a style=\"color: #333\"
                               href=\"" . "{$regionPath}/{$belongParentTable}/show/{$value}\">
                                {$belongParent[$this->belongSelfColumns[$key]]}
                            </a>";
                    }
                    else
                    {
                        $item[] = '';
                    }
                }
                elseif (array_key_exists("{$table}.{$key}", $this->enumColumns))
                {
                    $color = substr(md5($value), 0, 6);
                    $value = $this->i18n->get($value);
                    $item[] = "<i class='fa fa-square' style='color: #{$color}'></i> {$value}";
                }
                elseif (array_key_exists("{$table}.{$key}", $this->enumsColumns))
                {
                    $enums = $this->encryptor->jsonDecode($value, true);
                    $enums2 = [];
                    foreach ($enums as $enum)
                    {
                        $color = substr(md5($enum), 0, 6);
                        $enum = $this->i18n->get($enum);
                        $enums2[] = "<i class='fa fa-square' style='color: #{$color}'></i> {$enum}";
                    }
                    $item[] = implode(', ', $enums2);
                }
                elseif (array_key_exists("{$table}.{$key}", $this->levelColumns))
                {
                    $options = $this->encryptor->jsonDecode($this->levelColumns["{$table}.{$key}"], true);
                    $choices = [];
                    foreach ($options as $option => $level)
                    {
                        if (($level & $value) === $level)
                        {
                            $color = substr(md5($option), 0, 6);
                            $choices[] = "<i class='fa fa-square' style='color: #{$color}'></i> "
                                       . $this->i18n->get($option);
                        }
                    }
                    $item[] = implode(', ', $choices);
                }
                elseif (array_key_exists("{$table}.{$key}", $this->numberColumns))
                {
                    $item[] = isset($value)
                            ? '<span class="pull-right">' . (number_format($value, 10) + 0) . '</span>'
                            : '';
                }
                else
                {
                    $item[] = $value;
                }
            }

            $action = '';
            if (
                !isset($this->options->show) ||
                $this->options->show &&
                ($this->data->permission & SHOW_LEVEL) === SHOW_LEVEL
            )
            {
                $action .= "<a title=\"" . $this->i18n->show . "\"
                               style=\"color: #333\"
                               href=\"" . WEBSITE_ROOT . "{$modulePath}/show/{$item[0]}\"
                               id=\"link_item_show_{$item[0]}\">
                                <i class=\"fa fa-navicon\"></i>
                            </a>&nbsp;";
            }
            if (
                !isset($this->options->edit) ||
                $this->options->edit &&
                ($this->data->permission & EDIT_LEVEL) === EDIT_LEVEL
            )
            {
                $action .= "<a title=\"" . $this->i18n->edit . "\"
                               style=\"color: #333\"
                               href=\"" . WEBSITE_ROOT . "{$modulePath}/edit/{$item[0]}\"
                               id=\"link_item_edit_{$item[0]}\">
                                <i class=\"fa fa-pencil\"></i>
                            </a>&nbsp;";
                if (!isset($this->options->delete) || $this->options->delete)
                {
                    $action .= "<a title=\"" . $this->i18n->delete . "\"
                                   style=\"color: #333\"
                                   href=\"" . WEBSITE_ROOT . "{$modulePath}/edit/{$item[0]}?action=Delete\"
                                   id=\"link_item_delete_{$item[0]}\"
                                   onClick=\"return confirm('" . $this->i18n->areYouSureToDeleteThisItem . "')\">
                                    <i class=\"fa fa-trash\"></i>
                                </a>";
                }
            }

            $item[] = $action;
            $items[] = $item;
        }
        $result = $this->encryptor->jsonEncode([
            "draw" => isset($request->draw) ? intval($request->draw) : 0,
            "recordsTotal" => $total['total'],
            "recordsFiltered" => $filteredTotal['total'],
            "data" => $items
        ]);

        $this->logger->log(LogLevel::DEBUG, "Data: {$result}");
        echo $result;
        exit;
    }

    /**
     * @throws
     */
    private function getSelection($columns, $regionPath, $dbInfo)
    {
        $oracleDateFormat = ORA_DATE_FORMAT;
        $oracleDatetimeFormat = ORA_DATETIME_FORMAT;

        $sqlDateFormat = SQL_DATE_FORMAT;
        $sqlDatetimeFormat = SQL_DATETIME_FORMAT;

        $fields = $this->pluck($columns, 'db');
        $select = implode(',', $fields);
        foreach ($this->fields as $field)
        {
            $fieldChainCase = chain_case($field['field']);
            if ($field['type'] === "belong")
            {
                if (strpos($field['field'], ':') !== false)
                {
                    $fieldParts = explode(':', $field['field']);
                    $displayParts = explode(':', $field['display']);
                    $select = str_replace(
                        "{$displayParts[0]}_{$fieldParts[0]}",
                        "{$fieldParts[0]}.parent AS `{$displayParts[0]}_{$fieldParts[0]}`",
                        $select
                    );
                }
                else
                {
                    if ($dbInfo['database_info']['type'] === 'oracle')
                    {
                        $select = str_replace(
                            "{$field['field']}.{$field['display']}",
                            "'<a style=\"color: #333\"
                                 href=\"{$regionPath}/{$fieldChainCase}/show/' || {$field['field']}.id || '\">
                                  <i class=\"fa fa-navicon\"></i> ' || {$field['field']}.{$field['display']} ||
                             '</a>'
                             AS `{$field['field']}.{$field['display']}`",
                            $select
                        );
                    }
                    else
                    {
                        $select = str_replace(
                            "{$field['field']}.{$field['display']}",
                            "CONCAT(
                                '<a style=\"color: #333\"
                                    href=\"{$regionPath}/{$fieldChainCase}/show/', {$field['field']}.id, '\">
                                     <i class=\"fa fa-navicon\"></i>',
                                     {$field['field']}.{$field['display']},
                                     '</a>'
                             )
                             AS `{$field['field']}.{$field['display']}`",
                            $select
                        );
                    }
                }
            }
            if ($field['type'] === "weakbelong")
            {
                $fieldParts = explode(':', $field['field']);
                $fieldPartChainCase = chain_case($fieldParts[1]);
                if ($dbInfo['database_info']['type'] === 'oracle')
                {
                    $select = str_replace(
                        "{$fieldParts[0]}.{$field['display']}",
                        "'<a style=\"color: #333\"
                             href=\"{$regionPath}/{$fieldPartChainCase}/show/' || {$fieldParts[0]}.id || '\">
                              <i class=\"fa fa-navicon\"></i>' ||
                              {$fieldParts[0]}.{$field['display']} ||
                         '</a>'
                         AS `{$fieldParts[0]}.{$field['display']}`",
                        $select
                    );
                }
                else
                {
                    $select = str_replace(
                        "{$fieldParts[0]}.{$field['display']}",
                        "CONCAT(
                            '<a style=\"color: #333\"
                                href=\"{$regionPath}/{$fieldPartChainCase}/show/', {$fieldParts[0]}.id, '\">
                                 <i class=\"fa fa-navicon\"></i>',
                                 {$fieldParts[0]}.{$field['display']},
                            '</a>'
                         )
                         AS `{$fieldParts[0]}.{$field['display']}`",
                        $select
                    );
                }
            }
            if ($field['type'] === "checkbox")
            {
                if ($dbInfo['database_info']['type'] === 'oracle')
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "'<span style=\"font-family: \'FontAwesome\'; font-size: 15px; text-align: center\">' ||
                              DECODE({$field['table']}.{$field['field']}, 1, '&#xf046;', '&#xf096;') ||
                         '</span>'
                         AS `{$field['field']}`",
                        $select
                    );
                }
                else
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "CONCAT(
                            '<span style=\"font-family: \'FontAwesome\'; font-size: 15px; text-align: center\">',
                                IF({$field['table']}.{$field['field']} = 1, '&#xf046;', '&#xf096;'),
                            '</span>'
                         )
                         AS `{$field['field']}`",
                        $select
                    );
                }
            }
            if ($field['type'] === "email")
            {
                if ($dbInfo['database_info']['type'] === 'oracle')
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "'<a style=\"color: #333\"
                             href=\"mailto:' || {$field['table']}.{$field['field']} || '\">
                              <i class=\"fa fa-envelope\"></i> ' ||
                              {$field['table']}.{$field['field']} ||
                         '</a>'
                         AS `{$field['field']}`",
                        $select
                    );
                }
                else
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "CONCAT(
                            '<a style=\"color: #333\" href=\"mailto:', {$field['table']}.{$field['field']}, '\">
                                 <i class=\"fa fa-envelope\"></i> ',
                                 {$field['table']}.{$field['field']},
                            '</a>'
                         )
                         AS `{$field['field']}`",
                        $select
                    );
                }
            }
            if ($field['type'] === "phone")
            {
                if ($dbInfo['database_info']['type'] === 'oracle')
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "'<a style=\"color: #333\" href=\"tel:' || {$field['table']}.{$field['field']} || '\">
                             <i class=\"fa fa-phone\"></i> ' ||
                             {$field['table']}.{$field['field']} ||
                         '</a>'
                         AS `{$field['field']}`",
                        $select
                    );
                }
                else
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "CONCAT(
                            '<a style=\"color: #333\" href=\"tel:', {$field['table']}.{$field['field']}, '\">
                                 <i class=\"fa fa-phone\"></i> ',
                                 {$field['table']}.{$field['field']},
                             '</a>'
                         )
                         AS `{$field['field']}`",
                        $select
                    );
                }
            }
            if ($field['type'] === "link")
            {
                if ($dbInfo['database_info']['type'] === 'oracle')
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "'<a style=\"color: #333\" href=\"' || {$field['table']}.{$field['field']} || '\">' ||
                              {$field['table']}.{$field['field']} ||
                         '</a>'
                         AS `{$field['field']}`",
                        $select
                    );
                }
                else
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "CONCAT(
                            '<a style=\"color: #333\" href=\"', {$field['table']}.{$field['field']}, '\">',
                                {$field['table']}.{$field['field']},
                            '</a>'
                         ) AS `{$field['field']}`",
                        $select
                    );
                }
            }
            if ($field['type'] === "date")
            {
                if ($dbInfo['database_info']['type'] === 'oracle')
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "'<div class=\"text-center\">' ||
                            TO_CHAR({$field['table']}.{$field['field']}, '{$oracleDateFormat}') ||
                         '</div>'
                         AS `{$field['field']}`",
                        $select
                    );
                }
                else
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "CONCAT(
                            '<div class=\"text-center\">',
                                DATE_FORMAT({$field['table']}.{$field['field']}, '{$sqlDateFormat}'),
                            '</div>'
                         )
                         AS `{$field['field']}`",
                        $select
                    );
                }
            }
            if (in_array($field['type'], ['datetime', 'eventstart', 'eventend']))
            {
                if ($dbInfo['database_info']['type'] === 'oracle')
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "'<div class=\"text-center\">' ||
                            TO_CHAR({$field['table']}.{$field['field']}, '{$oracleDatetimeFormat}') ||
                         '</div>'
                         AS `{$field['field']}`",
                        $select
                    );
                }
                else
                {
                    $select = str_replace(
                        "{$field['table']}.{$field['field']}",
                        "CONCAT(
                            '<div class=\"text-center\">',
                                DATE_FORMAT({$field['table']}.{$field['field']}, '{$sqlDatetimeFormat}'),
                            '</div>'
                         )
                         AS `{$field['field']}`",
                        $select
                    );
                }
            }
        }
        // $select .= ",(select (count(*) + 1) from system_history where system_history.table = '{$field['table']}' and system_history.object = {$field['table']}.id and system_history.status != 'Created') as `version` ";
        if (SHOW_LAST_MODIFIED)
        {
            if ($dbInfo['database_info']['type'] === 'oracle')
            {
                $select .= ",(
                    select TO_CHAR(MAX(system_history.created_time), '{$oracleDatetimeFormat}')
                    from system_history
                    where table = '{$field['table']}'
                      and object = {$field['table']}.id
                )
                as `last_modified`";
            }
            else
            {
                $select .= ",(
                    select DATE_FORMAT(MAX(system_history.created_time), '{$sqlDatetimeFormat}')
                    from system_history
                    where table = '{$field['table']}'
                      and object = {$field['table']}.id
                )
                as `last_modified`";
            }
        }
        return $select;
    }

    /**
     * @throws
     */
    private function getColumns($table)
    {
        $columns = [];
        $columns[] = [
            'db' => "{$table}.id",
            'dt' => 0
        ];
        $count = 0;
        if (isset($this->filter))
        {
            foreach ($this->encryptor->jsonDecode($this->filter['selections'], true) as $selection)
            {
                foreach ($this->fields as $columnItem)
                {
                    if ($selection !== $columnItem['id'])
                    {
                        continue;
                    }

                    $this->getColumn($columnItem, $table, $columns, $count);
                }
            }
        }
        else
        {
            foreach ($this->fields as $columnItem)
            {
                $this->getColumn($columnItem, $table, $columns, $count);
            }
        }

        $this->logger->log(
            LogLevel::DEBUG,
            "Columns: " . $this->encryptor->jsonEncode($columns)
        );
        return $columns;
    }

    /**
     * @throws
     */
    private function getColumn($columnItem, $table, &$columns, &$count)
    {
        if ($columnItem['field'] === 'id')
        {
            $this->options = $this->encryptor->jsonDecode($columnItem['display'], true);
        }
        $count ++;
        if ($columnItem['type'] === 'checkbox')
        {
            $columns[] = [
                'db' => "{$table}.{$columnItem['field']}",
                'dt' => $count
            ];
            $this->checkboxColumns["{$table}.{$columnItem['field']}"] = $columnItem['display'];
        }
        elseif ($columnItem['type'] === 'belong')
        {
            if (strpos($columnItem['field'], ':') !== false)
            {
                $fieldParts = explode(':', $columnItem['field']);
                $displayParts = explode(':', $columnItem['display']);
                $columns[] = [
                    'db' => "{$displayParts[0]}_{$fieldParts[0]}",
                    'dt' => $count
                ];
                $this->belongSelfColumns["{$displayParts[0]}_{$fieldParts[0]}"] = $displayParts[1];
            }
            else
            {
                $columns[] = [
                    'db' => "{$columnItem['field']}.{$columnItem['display']}",
                    'dt' => $count
                ];
                $this->belongColumns["{$columnItem['field']}.{$columnItem['display']}"] = $columnItem['display'];
            }
        }
        elseif ($columnItem['type'] === 'weakbelong')
        {
            $fieldParts = explode(':', $columnItem['field']);
            $columns[] = [
                'db' => "{$fieldParts[0]}.{$columnItem['display']}",
                'dt' => $count
            ];
            $this->weakBelongColumns["{$columnItem['field']}.{$columnItem['display']}"] = $columnItem['display'];
        }
        elseif ($columnItem['type'] === 'self')
        {
            $columns[] = [
                'db' => "{$table}.{$columnItem['field']}",
                'dt' => $count
            ];
            $this->selfColumns["{$table}.{$columnItem['field']}"] = $columnItem['display'];
        }
        elseif ($columnItem['type'] === 'enum')
        {
            $columns[] = [
                'db' => "{$table}.{$columnItem['field']}",
                'dt' => $count
            ];
            $this->enumColumns["{$table}.{$columnItem['field']}"] = $columnItem['display'];
        }
        elseif ($columnItem['type'] === 'date')
        {
            $columns[] = [
                'db' => "{$table}.{$columnItem['field']}",
                'dt' => $count
            ];
            $this->dateColumns["{$table}.{$columnItem['field']}"] = $columnItem['display'];
        }
        elseif (in_array($columnItem['type'], ['datetime', 'eventstart', 'eventend']))
        {
            $columns[] = [
                'db' => "{$table}.{$columnItem['field']}",
                'dt' => $count
            ];
            $this->datetimeColumns["{$table}.{$columnItem['field']}"] = $columnItem['display'];
        }
        elseif (in_array($columnItem['type'], ['enums']))
        {
            $columns[] = [
                'db' => "{$table}.{$columnItem['field']}",
                'dt' => $count
            ];
            $this->enumsColumns["{$table}.{$columnItem['field']}"] = $columnItem['display'];
        }
        elseif (in_array($columnItem['type'], ['level']))
        {
            $columns[] = [
                'db' => "{$table}.{$columnItem['field']}",
                'dt' => $count
            ];
            $this->levelColumns["{$table}.{$columnItem['field']}"] = $columnItem['display'];
        }
        elseif (in_array($columnItem['type'], ['integer', 'float', 'double']))
        {
            $columns[] = [
                'db' => "{$table}.{$columnItem['field']}",
                'dt' => $count
            ];
            $this->numberColumns["{$table}.{$columnItem['field']}"] = $columnItem['display'];
        }
        elseif (!in_array($columnItem['type'], ['have', 'has']))
        {
            $columns[] = [
                'db' => "{$table}.{$columnItem['field']}",
                'dt' => $count
            ];
        }
    }

    /**
     * @throws
     */
    private function getGlobalFilter($request, $table, $columns, $dtColumns, $dbInfo)
    {
        $oracleDateFormat = ORA_DATE_FORMAT;
        $oracleDatetimeFormat = ORA_DATETIME_FORMAT;

        $sqlDateFormat = SQL_DATE_FORMAT;
        $sqlDatetimeFormat = SQL_DATETIME_FORMAT;

        $globalSearch = [];
        if (isset($request->search) && $request->search['value'] !== '')
        {
            $value = $request->search['value'];
            for ($i = 0, $ien = count($request->columns); $i < $ien; $i ++)
            {
                $requestColumn = $request->columns[$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];
                if ($requestColumn['searchable'] === 'true')
                {
                    if (array_key_exists($column['db'], $this->selfColumns))
                    {
                        $globalSearch[] = "{$column['db']} IN (
                            SELECT id `id`
                            FROM {$table}
                            WHERE {$this->selfColumns[$column['db']]} LIKE ?
                        )";
                        $this->filterParameters[] = "%{$value}%";
                    }
                    elseif (array_key_exists($column['db'], $this->belongSelfColumns))
                    {
                        $keyParts = explode('_', $column['db']);
                        $belongTable = $keyParts[1];
                        $globalSearch[] = "{$belongTable}.parent IN (
                            SELECT id `id`
                            FROM {$belongTable}
                            WHERE {$this->belongSelfColumns[$column['db']]} LIKE ?
                        )";
                        $this->filterParameters[] = "%{$value}%";
                    }
                    elseif (array_key_exists($column['db'], $this->dateColumns))
                    {
                        if ($dbInfo['database_info']['type'] === 'oracle')
                        {
                            $globalSearch[] = "TO_CHAR(
                                {$column['db']},
                                '{$oracleDateFormat}'
                            ) LIKE ?";
                        }
                        else
                        {
                            $globalSearch[] = "DATE_FORMAT(
                                {$column['db']},
                                '{$sqlDateFormat}'
                            ) LIKE ?";
                        }
                        $this->filterParameters[] = "%{$value}%";
                    }
                    elseif (array_key_exists($column['db'], $this->datetimeColumns))
                    {
                        if ($dbInfo['database_info']['type'] === 'oracle')
                        {
                            $globalSearch[] = "TO_CHAR(
                                {$column['db']},
                                '{$oracleDatetimeFormat}'
                            ) LIKE ?";
                        }
                        else
                        {
                            $globalSearch[] = "DATE_FORMAT(
                                {$column['db']},
                                '{$sqlDatetimeFormat}'
                            ) LIKE ?";
                        }
                        $this->filterParameters[] = "%{$value}%";
                    }
                    elseif (array_key_exists($column['db'], $this->enumsColumns))
                    {
                        $searchValues = explode(',', $value);
                        $columnSearchLocal = [];
                        foreach ($searchValues as $searchValue)
                        {
                            $columnSearchLocal[] = "{$column['db']} like ?";
                            $this->filterParameters[] = "%\"{$searchValue}%\"";
                        }
                        if (count($columnSearchLocal))
                        {
                            $globalSearch[] = "(" . implode(" OR ", $columnSearchLocal) .")";
                        }
                    }
                    elseif (array_key_exists($column['db'], $this->levelColumns))
                    {
                        $options = $this->encryptor->jsonDecode($this->levelColumns[$column['db']], true);
                        $searchValues = explode(',', $value);
                        $columnSearchLocal = [];
                        foreach ($searchValues as $searchValue)
                        {
                            if (isset($options[$searchValue]))
                            {
                                $columnSearchLocal[] = "({$column['db']} & ?) = ?";
                                $this->filterParameters[] = $options[$searchValue];
                                $this->filterParameters[] = $options[$searchValue];
                            }
                        }
                        if (count($columnSearchLocal))
                        {
                            $globalSearch[] = "(" . implode(" AND ", $columnSearchLocal) .")";
                        }
                    }
                    else
                    {
                        $globalSearch[] = "{$column['db']} LIKE ?";
                        $this->filterParameters[] = "%{$value}%";
                    }
                }
            }
        }
        return $globalSearch;
    }

    /**
     * @throws
     */
    private function getColumnFilter($request, $table, $columns, $dtColumns, $dbInfo)
    {
        $oracleDateFormat = ORA_DATE_FORMAT;
        $oracleDatetimeFormat = ORA_DATETIME_FORMAT;

        $sqlDateFormat = SQL_DATE_FORMAT;
        $sqlDatetimeFormat = SQL_DATETIME_FORMAT;

        $columnSearch = [];
        if (isset($request->columns))
        {
            for ($i = 0, $ien = count($request->columns); $i < $ien; $i ++)
            {
                $requestColumn = $request->columns[$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];
                $value = $requestColumn['search']['value'];
                if ($requestColumn['searchable'] === 'true' && $value !== '')
                {
                    if (array_key_exists($column['db'], $this->selfColumns))
                    {
                        $searchValues = explode(',', $value);
                        $columnSearchLocal = [];
                        foreach ($searchValues as $searchValue)
                        {
                            $columnSearchLocal[] = "{$column['db']} IN (
                                SELECT id `id`
                                FROM {$table}
                                WHERE {$this->selfColumns[$column['db']]} LIKE ?
                            )";
                            $this->filterParameters[] = "%{$searchValue}%";
                        }
                        $columnSearch[] = "(" . implode(" OR ", $columnSearchLocal) .")";
                    }
                    elseif (array_key_exists($column['db'], $this->belongSelfColumns))
                    {
                        $keyParts = explode('_', $column['db']);
                        $belongTable = $keyParts[1];
                        $columnSearch[] = "{$belongTable}.parent IN (
                            SELECT id `id`
                            FROM {$belongTable}
                            WHERE {$this->belongSelfColumns[$column['db']]} LIKE ?
                        )";
                        $this->filterParameters[] = "%{$value}%";
                    }
                    elseif (array_key_exists($column['db'], $this->belongColumns))
                    {
                        $searchValues = explode(',', $value);
                        $columnSearchLocal = [];
                        foreach ($searchValues as $searchValue)
                        {
                            $columnSearchLocal[] = "{$column['db']} = ?";
                            $this->filterParameters[] = $searchValue;
                        }
                        if (count($columnSearchLocal))
                        {
                            $columnSearch[] = "(" . implode(" OR ", $columnSearchLocal) .")";
                        }
                    }
                    elseif (array_key_exists($column['db'], $this->checkboxColumns))
                    {
                        $searchValues = explode(',', $value);
                        $columnSearchLocal = [];
                        foreach ($searchValues as $searchValue)
                        {
                            $columnSearchLocal[] = "{$column['db']} = ?";
                            $this->filterParameters[] = $searchValue;
                        }
                        if (count($columnSearchLocal))
                        {
                            $columnSearch[] = "(" . implode(" OR ", $columnSearchLocal) .")";
                        }
                    }
                    elseif (array_key_exists($column['db'], $this->enumColumns))
                    {
                        $searchValues = explode(',', $value);
                        $columnSearchLocal = [];
                        foreach ($searchValues as $searchValue)
                        {
                            $columnSearchLocal[] = "{$column['db']} = ?";
                            $this->filterParameters[] = $searchValue;
                        }
                        if (count($columnSearchLocal))
                        {
                            $columnSearch[] = "(" . implode(" OR ", $columnSearchLocal) .")";
                        }
                    }
                    elseif (array_key_exists($column['db'], $this->enumsColumns))
                    {
                        $searchValues = explode(',', $value);
                        $columnSearchLocal = [];
                        foreach ($searchValues as $searchValue)
                        {
                            $columnSearchLocal[] = "{$column['db']} like ?";
                            $this->filterParameters[] = "%\"{$searchValue}%\"";
                        }
                        if (count($columnSearchLocal))
                        {
                            $columnSearch[] = "(" . implode(" OR ", $columnSearchLocal) .")";
                        }
                    }
                    elseif (array_key_exists($column['db'], $this->levelColumns))
                    {
                        $searchValues = explode(',', $value);
                        $columnSearchLocal = [];
                        foreach ($searchValues as $searchValue)
                        {
                            $columnSearchLocal[] = "({$column['db']} & ?) = ?";
                            $this->filterParameters[] = $searchValue;
                            $this->filterParameters[] = $searchValue;
                        }
                        if (count($columnSearchLocal))
                        {
                            $columnSearch[] = "(" . implode(" AND ", $columnSearchLocal) .")";
                        }
                    }
                    elseif (array_key_exists($column['db'], $this->dateColumns))
                    {
                        if ($dbInfo['database_info']['type'] === 'oracle')
                        {
                            $columnSearch[] = "TO_CHAR(
                                {$column['db']},
                                '{$oracleDateFormat}'
                            ) LIKE ?";
                        }
                        else
                        {
                            $columnSearch[] = "DATE_FORMAT(
                                {$column['db']},
                                '{$sqlDateFormat}'
                            ) LIKE ?";
                        }
                        $this->filterParameters[] = "%{$value}%";
                    }
                    elseif (array_key_exists($column['db'], $this->datetimeColumns))
                    {
                        if ($dbInfo['database_info']['type'] === 'oracle')
                        {
                            $columnSearch[] = "TO_CHAR(
                                {$column['db']},
                                '{$oracleDatetimeFormat}'
                            ) LIKE ?";
                        }
                        else
                        {
                            $columnSearch[] = "DATE_FORMAT(
                                {$column['db']},
                                '{$sqlDatetimeFormat}'
                            ) LIKE ?";
                        }
                        $this->filterParameters[] = "%{$value}%";
                    }
                    else
                    {
                        $columnSearch[] = "{$column['db']} LIKE ?";
                        $this->filterParameters[] = "%{$value}%";
                    }
                }
            }
        }
        return $columnSearch;
    }

    /**
     * @throws
     */
    private function getFilter($request, $columns, $dbInfo)
    {
        $module = $this->env->module;

        $this->filterParameters = [];
        $table = snake_case($module);
        $dtColumns = $this->pluck($columns, 'dt');

        $globalSearch = $this->getGlobalFilter(
            $request,
            $table, $columns,
            $dtColumns, $dbInfo
        );
        $columnSearch = $this->getColumnFilter(
            $request,
            $table, $columns,
            $dtColumns, $dbInfo
        );

        $where = '';
        if (count($globalSearch))
        {
            $where = '(' . implode(' OR ', $globalSearch) . ')';
        }
        if (count($columnSearch))
        {
            $where = strlen($where)
                ? "{$where} AND " . implode(' AND ', $columnSearch)
                : implode(' AND ', $columnSearch);
        }
        $where = strlen($where) ? $where : '1=1';
        if (isset($this->filter))
        {
            $where .= " and {$this->filter['conditions']}";
        }

        $this->logger->log(LogLevel::DEBUG, "Where: {$where}");
        return $where;
    }

    /**
     * @throws
     */
    private function getOrderBy($request, $columns)
    {
        $order = '';
        /*
        $columns[] = [
            'db' => "version",
            'dt' => count($columns)
        ];
        */
        $columns[] = [
            'db' => "created_time",
            'dt' => count($columns)
        ];
        if (isset($request->order) && count($request->order))
        {
            $orderBy = [];
            $dtColumns = $this->pluck($columns, 'dt');
            // $dtColumns[] = 'version';
            $dtColumns[] = 'created_time';
            for ($i = 0, $ien = count($request->order); $i < $ien; $i ++)
            {
                // Convert the column index into the column data property
                $columnIdx = intval($request->order[$i]['column']);
                $requestColumn = $request->columns[$columnIdx];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];
                if ($requestColumn['orderable'] === 'true')
                {
                    $dir = $request->order[$i]['dir'] === 'asc' ? 'ASC' : 'DESC';
                    $orderBy[] = "{$column['db']} {$dir}";
                }
            }
            $order = implode(', ', $orderBy);
        }
        return $order;
    }

    /**
     * @throws
     */
    private function pluck($array, $property)
    {
        $out = [];
        for ($i = 0, $count = count($array); $i < $count; $i++)
        {
            $out[] = $array[$i][$property];
        }
        return $out;
    }

    /**
     * Event when Get All Items Ajax is called
     *
     * @throws
     */
    public function onListItems($items)
    {
        $this->data->items = $items;
    }
}
