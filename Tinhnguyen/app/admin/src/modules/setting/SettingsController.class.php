<?php

namespace Lza\App\Admin\Modules\Setting;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseController;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SettingsController extends BaseController
{
    /**
     * Get Selected Fields from database
     *
     * @throws
     */
    public function getFields($section, $columns = null, $conditions = null)
    {
        $model = ModelPool::getModel('lzasetting');
        $items = $model->where("lzasection.id", $section);
        $items->order('lzasetting.order_by');
        $items = $columns === null ? $items : $items->select($columns);
        $items = $conditions === null ? $items : $items->where($conditions);

        return $this->formatOutputs($items);
    }

    /**
     * Save changes to database
     *
     * @throws
     */
    public function save($callback, $items)
    {
        $model = ModelPool::getModel('lzasetting');
        foreach ($items as $key => $value)
        {
            $setting = $model->where("lzasetting.id", $key)->fetch();
            if ($setting)
            {
                $value = $this->formatInput($setting['type'], $value);
                $result = $setting->update([
                    "lzasetting.value" => $value
                ]);

                if (!$result)
                {
                    $callback->onError('Failed to update ' . $setting['id']);
                    return;
                }
            }
        }
        $callback->onSaveSuccess();
    }

    /**
     * Format input before update to database
     *
     * @throws
     */
    public function formatInput($type, $value)
    {
        if ($type === 'date')
        {
            $value = !$value || $value === null ? null : $value->format('Y-m-d');
        }
        elseif (in_array($type, ['datetime', 'eventstart', 'eventend']))
        {
            $value = !$value || $value === null ? null : $value->format('Y-m-d H:i:s');
        }
        elseif ($type === 'checkbox' && $value === "")
        {
            $value = 0;
        }
        if (strcmp($type, 'html') !== 0)
        {
            $value = strip_tags($value);
        }
        return $value;
    }

    /**
     * Format outnput after retrieved from database
     *
     * @throws
     */
    public function formatOutputs($items)
    {
        foreach ($items as $item)
        {
            if ($item['type'] === 'date')
            {
                $item['value'] = strlen($item['value']) === 0
                    ? null : date_create_from_format('Y-m-d', $item['value']);
            }
            elseif (in_array($item['type'], ['datetime', 'eventstart', 'eventend']))
            {
                $item['value'] = strlen($item['value']) === 0
                    ? null : date_create_from_format('Y-m-d H:i:s', $item['value']);
            }
            elseif ($item['type'] === 'password')
            {
                $item['value'] = '';
            }
        }
        return $items;
    }
}
