<?php

namespace Lza\LazyAdmin\Utility\Data;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Setting gets and sets System Settings in the database
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Setting
{
    use Singleton;

    /**
     * @throws
     */
    public function __get($key)
    {
        $key = snake_case($key);
        $model = ModelPool::getModel('lzasetting');
        $settings = $model->where("lzasetting.id = ?", $key);
        $setting = $settings->select("lzasetting.value `value`")->fetch();
        return $setting !== false ? $setting['value'] : $key;
    }

    /**
     * @throws
     */
    public function __set($key, $value = '')
    {
        $key = snake_case($key);
        $model = ModelPool::getModel('lzasetting');
        $settings = $model->where("lzasetting.id = ?", $key);
        $setting = $settings->select("lzasetting.value `value`")->fetch();
        $setting['value'] = $value;
        return $setting->update();
    }
}
