<?php

namespace Lza\App\Admin\Modules\Dashboard\Content;


use Lza\App\Admin\Modules\AdminView;
use Lza\Config\Models\ModelPool;

/**
 * Process Dashboard page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DashboardContentView extends AdminView
{
    /**
     * @throws
     */
    protected function hasPermissionToAccess()
    {
        return true;
    }

    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreate()
    {
        parent::onCreate();
        $model = ModelPool::getModel('Lzamodule');
        $model2 = ModelPool::getModel('Lzafield');
        $modules = $model->where('db_id', 'main');
        $statistics = [];
        $this->data->table = ['db_id' => 'main'];
        foreach ($modules as $module)
        {
            $fields = $model2->where('lzamodule_id', $module['id'])->select('
                lzafield.*,
                lzamodule.id `table`,
                lzafield.id lzafield_id
            ');
            $result = $this->doGetStatistics(
                $this->session->get('user.id'),
                $module['id'],
                array_map('iterator_to_array', iterator_to_array($fields))
            );
            $statistics = array_merge(
                $statistics,
                array_map('iterator_to_array', iterator_to_array($result))
            );
        }
        $this->data->statistics = $statistics;
        $this->doGetItemCounts();
    }

    /**
     * Event when CSSes is loading
     *
     * @throws
     */
    protected function onLoadStyles()
    {
        parent::onLoadStyles();
        $sbAdmin = WEBSITE_ROOT . 'vendors/iron-summit-media/startbootstrap-sb-admin-2/';
        $this->data->styles['body'][] = "{$sbAdmin}css/timeline.css";
        $this->data->styles['body'][] = "{$sbAdmin}bower_components/morrisjs/morris.css";
    }

    /**
     * Event when Javascript is loading
     *
     * @throws
     */
    protected function onLoadScripts()
    {
        parent::onLoadScripts();
        $sbAdmin = WEBSITE_ROOT . 'vendors/iron-summit-media/startbootstrap-sb-admin-2/';
        $scripts = [
            "{$sbAdmin}bower_components/flot/excanvas.min.js",
            "{$sbAdmin}bower_components/flot/jquery.flot.js",
            "{$sbAdmin}bower_components/flot/jquery.flot.pie.js",
            "{$sbAdmin}bower_components/flot/jquery.flot.resize.js",
            "{$sbAdmin}bower_components/flot/jquery.flot.time.js",
            "{$sbAdmin}bower_components/flot/jquery.flot.simbols.js",
            "{$sbAdmin}bower_components/flot/jquery.flot.categories.js",
            "{$sbAdmin}bower_components/flot/jquery.flot.axislabels.js",
            "{$sbAdmin}bower_components/flot.tooltip/js/jquery.flot.tooltip.min.js"
        ];
        foreach ($scripts as $script)
        {
            $this->data->scripts['body'][] = $script;
        }
    }
}
