<?php

namespace Lza\App\Admin\Modules\General\Tree;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait TreeViewTrait
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
        $this->data->permission = $this->permission->hasPermission($user, $tableId, EDIT_LEVEL);

        return $this->data->permission !== false ? true : parent::hasPermissionToAccess();
    }

    /**
     * Event when Javascript is loading
     *
     * @throws
     */
    protected function onLoadScripts()
    {
        parent::onLoadScripts();
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'libraries/mbraak/jqtree/tree.jquery.js';
    }

    /**
     * Event when Show All Ajax is calling
     *
     * @throws
     */
    protected function onShowAll()
    {
        $node = isset($this->request->get->node) ? $this->request->get->node : null;
        $this->doShowAll($node, $this->data->table, $this->data->fields);
    }

    /**
     * Event when Move Item Ajax is calling
     *
     * @throws
     */
    protected function onMoveItem()
    {
        $this->doMoveItem(
            $this->request->post->node,
            $this->request->post->target,
            $this->request->post->position,
            $this->data->table,
            $this->data->fields
        );
    }
}
