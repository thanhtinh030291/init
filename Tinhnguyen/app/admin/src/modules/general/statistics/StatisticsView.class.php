<?php

namespace Lza\App\Admin\Modules\General\Statistics;


use Lza\App\Admin\Modules\AdminView;

/**
 * Process Statistics page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class StatisticsView extends AdminView
{
    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreate()
    {
        parent::onCreate();

        $this->data->table = $this->doGetTable($this->module);
        if (!$this->data->table || $this->data->table['enabled'] !== 'Yes')
        {
            $view = $this->doGetView($this->module);
            if ($view !== false)
            {
                $requestUri = $_SERVER['REQUEST_URI'];
                $module = chain_case($this->module);
                $view = chain_case($view['name']);
                $link = str_replace($module, $view, $requestUri);
                header("location: {$link}");
                exit();
            }

            $this->setError('Module Not Found!');
            $this->setContentView('general/listall/Error.html');
            return;
        }

        $settings = $this->data->table['settings'];
        $this->data->options = empty($settings) ? [] : $this->encryptor->jsonDecode($settings);
        $this->data->fields = $this->doGetTableFields($this->module);
    }

    protected function hasPermissionToAccess()
    {
        $this->data->table = $this->doGetTable($this->module);
        $tableId = $this->data->table['id'];
        $user = $this->session->get('user.username');
        $this->data->permission = $this->permission->hasPermission($user, $tableId, LIST_LEVEL);

        return $this->data->permission !== false ? true : parent::hasPermissionToAccess();
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

    /**
     * Event which is triggered if no action is performed
     *
     * @throws
     */
    protected function onNoActionActive()
    {
        $this->data->statistics = $this->doGetStatistics(
            $this->session->get('user.id'),
            $this->data->table['id'],
            $this->data->fields
        );
    }

    /**
     * Event when Change Language button is clicked
     *
     * @throws
     */
    public function onBtnChangeLanguageClick()
    {
        parent::onBtnChangeLanguageClick();
        $this->onNoActionActive();
    }

    /**
     * Event when Add Pie Chart button is clicked
     *
     * @throws
     */
    public function onBtnAddPieChartClick()
    {
        $this->doAddPieChart(
            $this->request->post->addName,
            $this->session->get('user.id'),
            $this->data->table['id'],
            $this->request->post->addField,
            $this->request->post->addWidth,
            $this->request->post->addOrder
        );
        header("location: {$_SERVER['REQUEST_URI']}");
        exit();
    }

    /**
     * Event when Update Pie Chart button is clicked
     *
     * @throws
     */
    public function onBtnUpdatePieChartClick()
    {
        $this->doUpdatePieChart(
            $this->request->post->editId,
            $this->request->post->editName,
            $this->request->post->editWidth,
            $this->request->post->editOrder
        );
        header("location: {$_SERVER['REQUEST_URI']}");
        exit();
    }

    /**
     * Event when Add Bar Chart button is clicked
     *
     * @throws
     */
    public function onBtnAddBarChartClick()
    {
        $this->doAddBarChart(
            $this->request->post->addName,
            $this->session->get('user.id'),
            $this->data->table['id'],
            $this->request->post->addField,
            $this->request->post->addType,
            $this->request->post->addWidth,
            $this->request->post->addOrder
        );
        header("location: {$_SERVER['REQUEST_URI']}");
        exit();
    }

    /**
     * Event when Update Bar Chart button is clicked
     *
     * @throws
     */
    public function onBtnUpdateBarChartClick()
    {
        $this->doUpdateBarChart(
            $this->request->post->editId,
            $this->request->post->editName,
            $this->request->post->editType,
            $this->request->post->editWidth,
            $this->request->post->editOrder
        );
        header("location: {$_SERVER['REQUEST_URI']}");
        exit();
    }

    /**
     * Event when Add Line or Area Chart button is clicked
     *
     * @throws
     */
    public function onBtnAddLineAreaChartClick()
    {
        $this->doAddLineAreaChart(
            $this->request->post->addName,
            $this->session->get('user.id'),
            $this->data->table['id'],
            $this->request->post->addField,
            $this->request->post->addType,
            $this->request->post->addExtra,
            $this->request->post->addWidth,
            $this->request->post->addOrder
        );
        header("location: {$_SERVER['REQUEST_URI']}");
        exit();
    }

    /**
     * Event when Update Line or Area Chart button is clicked
     *
     * @throws
     */
    public function onBtnUpdateLineAreaChartClick()
    {
        $this->doUpdateLineAreaChart(
            $this->request->post->editId,
            $this->request->post->editName,
            $this->request->post->editType,
            $this->request->post->editExtra,
            $this->request->post->editWidth,
            $this->request->post->editOrder
        );
        header("location: {$_SERVER['REQUEST_URI']}");
        exit();
    }

    /**
     * Event when Delete button is clicked
     *
     * @throws
     */
    public function onBtnDeleteClick()
    {
        $this->doDelete($this->request->post->itemId);
        header("location: {$_SERVER['REQUEST_URI']}");
        exit();
    }
}
