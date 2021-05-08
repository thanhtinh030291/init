<?php

namespace Lza\App\Admin\Modules\General\Show;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait ShowViewTrait
{
    /**
     * @var array List of inputs to be shown
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

        $this->onCreateForm();
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
            SHOW_LEVEL
        );

        return $this->data->permission !== false
                ? true : parent::hasPermissionToAccess();
    }

    /**
     * Event when CSSes is loading
     *
     * @throws
     */
    protected function onLoadStyles()
    {
        parent::onLoadStyles();

        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'vendors/select2/select2/dist/css/select2.min.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'admin-res/styles/select2-bootstrap.min.css';
        $this->data->styles['body'][] = WEBSITE_ROOT
                . 'admin-res/styles/show.css';
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
}
