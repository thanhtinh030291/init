<?php

namespace Lza\App\Admin\Modules\General\Statistics;


use Lza\App\Admin\Modules\AdminPresenter;
use Lza\Config\Models\ModelPool;

/**
 * Handle Default action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class StatisticsPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Get Charts request
     *
     * @throws
     */
    public function doGetStatistics($userId, $moduleId, $fields)
    {
        $this->getItemCounts($moduleId);

        $model = ModelPool::getModel('lzastatistic');
        $statistics = $model->where(
            "user_id = ? and
             lzamodule_id = ?",
            $userId,
            $moduleId
        );
        foreach ($statistics as $statistic)
        {
            $field = $fields[$statistic['lzafield_id']];

            $refTable = $this->getReferenceTable($field);
            $model = ModelPool::getModel($refTable);

            if (strlen($statistic['conditions']) > 0)
            {
                $refItems = $field['type'] === 'enum'
                        ? $this->encryptor->jsonDecode($statistic['conditions'], true)
                        : $model->where($statistic['conditions']);
            }
            else
            {
                $refItems = $field['type'] === 'enum'
                        ? $this->encryptor->jsonDecode($field['display'], true)
                        : $model->where('1=1');
            }

            if (in_array($statistic['type'], ['Pie Chart']))
            {
                $statistic['statistic_items'] = $this->generatePieChartData(
                    $field, $refItems
                );
            }
            elseif (in_array($statistic['type'], ['Vertical Bar Chart']))
            {
                $statistic['statistic_items'] = $this->generateVerticalBarChartData(
                    $field, $refItems
                );
            }
            elseif (in_array($statistic['type'], ['Horizontal Bar Chart']))
            {
                $statistic['statistic_items'] = $this->generateHorizontalBarChartData(
                    $field, $refItems
                );
            }
            elseif (
                strpos($statistic['type'], 'Area Chart') !== false ||
                strpos($statistic['type'], 'Line Chart') !== false
            )
            {
                $statistic['statistic_items'] = $this->generateLineAreaChartData(
                    $statistic['type'],
                    $field,
                    $refItems,
                    $statistic['extra']
                );
            }
        }

        return $statistics;
    }

    /**
     * @throws
     */
    private function getItemCounts($id)
    {
        $this->data->itemCounts = [];

        $model = ModelPool::getModel('lzamodule');
        $modules = $model->where('id = ?', $id);
        $modules->select('id,single');
        $module = $modules->fetch();
        $sql = $this->sql->moduleItemCount([
            'id' => str_replace("'", "''", $module['id']),
            'single' => str_replace("'", "''", $module['single'])
        ]);

        $result = $this->sql->query($sql, [], $this->data->table['db_id']);
        foreach ($result as $item)
        {
            $this->data->itemCounts[$item['action']] = $item['count'];
        }
    }

    /**
     * @throws
     */
    private function getEntityTable($field)
    {
        if ($field['type'] === 'have')
        {
            return strpos($field['field'], $field['table']) === 0
                    ? $field['table']
                    : trim(
                        str_replace(
                            $field['table'], '',
                            $field['field']),
                        '_'
                    );
        }
        elseif (in_array($field['type'], [
            'belong', 'weakbelong', 'enum',
            'date', 'datetime', 'eventstart', 'eventend'
        ]))
        {
            return $field['table'];
        }
        elseif (in_array($field['type'], ['has']))
        {
            return $field['field'];
        }
        return $field['field'];
    }

    /**
     * @throws
     */
    private function getReferenceTable($field)
    {
        if ($field['type'] === 'have')
        {
            return strpos($field['field'], $field['table']) === 0
                    ? trim(
                        str_replace(
                            $field['table'], '',
                            $field['field']
                        ),
                        '_'
                    )
                    : $field['table'];
        }
        elseif (in_array($field['type'], ['belong', 'enum']))
        {
            return $field['field'];
        }
        elseif (in_array($field['type'], ['weakbelong']))
        {
            $fieldParts = explode(':', $field['field']);
            return $fieldParts[0];
        }
        elseif (in_array($field['type'], ['has']))
        {
            return $field['table'];
        }
        return $field['table'];
    }

    /**
     * @throws
     */
    private function generatePieChartData($field, $refItems)
    {
        $items = [];
        foreach ($refItems as $refItem)
        {
            $item = [];
            if ($field['type'] === 'have')
            {
                $item['label'] = $refItem[$field['statistic']];
                $item['data'] = count($refItem->{$field['field']}());
            }
            elseif ($field['type'] === 'enum')
            {
                $fieldTable = $this->getEntityTable($field);
                $model = ModelPool::getModel($fieldTable);
                $item['label'] = $this->i18n->get($refItem);
                $item['data'] = count($model->where($field['field'], $refItem));
            }
            else
            {
                $fieldTable = $this->getEntityTable($field);
                $item['label'] = $refItem[$field['statistic']];
                $item['data'] = count($refItem->$fieldTable());
            }
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @throws
     */
    private function generateVerticalBarChartData($field, $refItems)
    {
        $items = [];
        foreach ($refItems as $refItem)
        {
            $item = [];
            if ($field['type'] === 'have')
            {
                $item[] = $refItem[$field['statistic']];
                $item[] = count($refItem->{$field['field']}());
            }
            elseif ($field['type'] === 'enum')
            {
                $fieldTable = $this->getEntityTable($field);
                $model = ModelPool::getModel($fieldTable);
                $item[] = $this->i18n->get($refItem);
                $item[] = count($model->where($field['field'], $refItem));
            }
            else
            {
                $fieldTable = $this->getEntityTable($field);
                $item[] = $refItem[$field['statistic']];
                $item[] = count($refItem->$fieldTable());
            }
            $items[] = $item;
        }
        return [['data' => $items]];
    }

    /**
     * @throws
     */
    private function generateHorizontalBarChartData($field, $refItems)
    {
        $items = [];
        foreach ($refItems as $refItem)
        {
            $item = [];
            if ($field['type'] === 'have')
            {
                $item[] = count($refItem->{$field['field']}());
                $item[] = $refItem[$field['statistic']];
            }
            elseif ($field['type'] === 'enum')
            {
                $fieldTable = $this->getEntityTable($field);
                $model = ModelPool::getModel($fieldTable);
                $item[] = count($model->where($field['field'], $refItem));
                $item[] = $this->i18n->get($refItem);
            }
            else
            {
                $fieldTable = $this->getEntityTable($field);
                $item[] = count($refItem->$fieldTable());
                $item[] = $refItem[$field['statistic']];
            }
            $items[] = $item;
        }
        return [[
            'data' => $items
        ]];
    }

    /**
     * @throws
     */
    private function generateLineAreaChartData($type, $field, $refTable, $extra)
    {
        $fieldTable = $this->getEntityTable($field);

        $elements = strlen($extra) > 0 ?
                $this->encryptor->jsonDecode(htmlspecialchars_decode($extra), true) : [];

        $selections = [];
        foreach ($elements as $element)
        {
            foreach ($element as $name => $value)
            {
                $selections[] = "{$value} as {$name}";
            }
        }
        $selection = implode(',', $selections);

        if ($type === 'Daily Line Chart')
        {
            $model = ModelPool::getModel($fieldTable);
            $data = $model->where(
                "{$field['field']} between DATE_SUB(NOW(), INTERVAL 2 WEEK) and NOW()"
            );
            $data->select("
                {$field['field']} `{$field['field']}`,
                concat(
                    concat(
                        year({$field['field']}),
                        month({$field['field']})
                    ),
                    day({$field['field']})
                ) as `legend`,
                {$selection}
            ");
        }
        elseif ($type === 'Weekly Line Chart')
        {
            $model = ModelPool::getModel($fieldTable);
            $data = $model->where(
                "{$field['field']} between DATE_SUB(NOW(), INTERVAL 2 MONTH) and NOW()"
            );
            $data->select("
                {$field['field']} `{$field['field']}`,
                concat(
                    concat(
                        year({$field['field']}),
                        month({$field['field']})
                    ),
                    week({$field['field']})
                ) as `legend`,
                {$selection}
            ");
        }
        elseif ($type === 'Monthly Line Chart')
        {
            $model = ModelPool::getModel($fieldTable);
            $data = $model->where(
                "{$field['field']} BETWEEN DATE_SUB(NOW(), INTERVAL 2 QUARTER) and NOW()"
            );
            $data->select("
                {$field['field']} `{$field['field']}`,
                concat(
                    year({$field['field']}),
                    month({$field['field']})
                ) as `legend`,
                {$selection}
            ");
        }
        elseif ($type === 'Quarterly Line Chart')
        {
            $model = ModelPool::getModel($fieldTable);
            $data = $model->where(
                "{$field['field']} BETWEEN DATE_SUB(NOW(), INTERVAL 2 YEAR) and NOW()"
            );
            $data->select("
                {$field['field']} `{$field['field']}`,
                concat(
                    year({$field['field']}),
                    quarter({$field['field']})
                ) as `legend`,
                {$selection}
            ");
        }
        elseif ($type === 'Yearly Line Chart')
        {
            $model = ModelPool::getModel($fieldTable);
            $data = $model->where(
                "{$field['field']} BETWEEN DATE_SUB(NOW(), INTERVAL 10 YEAR) and NOW()"
            );
            $data->select("
                {$field['field']} `{$field['field']}`,
                year({$field['field']}) as `legend`,
                {$selection}
            ");
        }

        $data->group("legend");
        $data->order($field['field']);

        $results = [];
        foreach ($elements as $element)
        {
            foreach ($element as $name => $value)
            {
                $result = [];
                foreach ($data as $item)
                {
                    $result[] = [
                        strtotime($item[$field['field']]) * 1000,
                        $item[$name]
                    ];
                }
                $results[] = $result;
            }
        }

        return $results;
    }
}
