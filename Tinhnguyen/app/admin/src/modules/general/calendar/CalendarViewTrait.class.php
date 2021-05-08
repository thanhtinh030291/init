<?php

namespace Lza\App\Admin\Modules\General\Calendar;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait CalendarViewTrait
{
    /**
     * @var integer Item's ID to be edited
     */
    protected $id;

    /**
     * @var integer Setting's ID to be deleted
     */
    protected $item = [];

    /**
     * @var array Many to Many Reference IDs
     */
    protected $many = [];

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
                $link = str_replace(
                    chain_case($this->module),
                    chain_case($view['name']),
                    $_SERVER['REQUEST_URI']
                );
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

    /**
     * @throws
     */
    protected function hasPermissionToAccess()
    {
        $this->data->table = $this->doGetTable($this->module);
        $this->data->permission = $this->permission->hasPermission(
            $this->session->get('user.username'),
            $this->data->table['id'],
            LIST_LEVEL
        );

        return $this->data->permission !== false
                ? true
                : parent::hasPermissionToAccess();
    }

    /**
     * @throws
     */
    protected function loadViewScripts()
    {
        $this->data->scripts['master'][] = WEBSITE_ROOT
                . 'libraries/fullcalendar/lib/moment.min.js';
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'libraries/fullcalendar/fullcalendar.min.js';
    }

    /**
     * Event when Get Items Ajax is called
     *
     * @throws
     */
    protected function onGetItems()
    {
        $this->doGetItems(
            $this->data->table,
            $this->data->fields,
            $this->request->get->start,
            $this->request->get->end
        );
        exit;
    }

    /**
     * Event when Get Item Ajax is called
     *
     * @throws
     */
    protected function onGetItem()
    {
        $this->doGetItem($this->request->get->itemid);
        exit;
    }
}
