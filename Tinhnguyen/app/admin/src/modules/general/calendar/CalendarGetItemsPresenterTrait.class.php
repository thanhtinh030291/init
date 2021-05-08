<?php

namespace Lza\App\Admin\Modules\General\Calendar;


use Lza\Config\Models\ModelPool;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait CalendarGetItemsPresenterTrait
{
    /**
     * Validate inputs and do Get All Records request
     *
     * @throws
     */
    public function doGetItems($table, $fields, $from, $to)
    {
        $model = ModelPool::getModel($table['id']);
        $displays = explode(',', $table['display']);
        $groups = $displays;
        $conditions = [];
        $parameters = [];
        foreach ($fields as $field)
        {
            $types = ['date', 'datetime', 'eventstart', 'eventend'];
            if (in_array($field['type'], $types))
            {
                $groups[] = $field['field'];
                $conditions[] = "{$field['field']} BETWEEN ? AND ?";
                $parameters[] = $from;
                $parameters[] = $to;
            }
        }

        $items = call_user_func_array(
            [$model, 'where'],
            array_merge(
                [implode(' OR ', $conditions)],
                $parameters
            )
        );
        $items->select(implode(',', array_merge(['min(id) `id`'], $groups)));
        $items->group(implode(',', $groups));
        $events = [];
        foreach ($items as $item)
        {
            foreach ($fields as $field)
            {
                $ofLabel = $this->i18n->of;
                if (
                    in_array($field['type'], ['date', 'datetime']) &&
                    strlen($item[$field['field']]) > 0
                )
                {
                    $title = "{$field["single{$this->session->lzalanguage}"]} {$ofLabel} {$item[$table['display']]}";
                    $events[] = [
                        'id' => $field['id'] . $item['id'],
                        'title' => $title,
                        'start' => $item[$field['field']],
                        'end' => $item[$field['field']],
                        'color' => '#' . color_encode($field['field']),
                        'itemid' => $item['id']
                    ];
                }
                elseif (
                    in_array($field['type'], ['eventstart']) &&
                    strlen($item[$field['field']]) > 0
                )
                {
                    foreach ($fields as $field2)
                    {
                        if (in_array($field2['type'], ['eventend']))
                        {
                            $events[] = [
                                'id' => $field['id'] . $item['id'],
                                'title' => "{$field["single{$this->session->lzalanguage}"]}"
                                         . "{$ofLabel} {$item[$table['display']]}",
                                'start' => $item[$field['field']],
                                'end' => $item[$field2['field']],
                                'color' => '#' . color_encode(
                                    $field['field'] . $field2['field']
                                ),
                                'itemid' => $item['id']
                            ];
                        }
                    }
                }
            }
        }
        echo $this->encryptor->jsonEncode($events);
    }
}
