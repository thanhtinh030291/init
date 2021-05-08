<?php

namespace Lza\App\Admin\Modules\General\Edit;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait EditViewTrait
{
    /**
     * @var array List of View's inputs
     */
    protected $inputs = [];

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

        if (!$this->isAjax())
        {
            $this->onCreateForm();
        }
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
            EDIT_LEVEL
        );

        return $this->data->permission !== false
                ? true :
                parent::hasPermissionToAccess();
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
               . 'vendors/select2/select2/dist/js/select2.full.min.js';
    }

    /**
     * Event when Change Language button is clicked
     *
     * @throws
     */
    public function onBtnChangeLanguageClick()
    {
        parent::onBtnChangeLanguageClick();
    }

    /**
     * @throws
     */
    public function onBtnUpdateClick()
    {
        $this->doSave(
            $this->data->editForm->getValues(),
            $this->data->fields,
            $this->data->editForm->getManyToManyValues()
        );
    }

    /**
     * @throws
     */
    public function onBtnDeleteClick()
    {
        $id = $this->env->id;

        $isAjax = true;
        if (isset($this->env->action) && $this->env->action === 'delete')
        {
            $isAjax = false;
        }

        $this->doDelete($id, $isAjax);
        if (!isset($this->error))
        {
            $this->navigateToModule();
        }
    }
}
