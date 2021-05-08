<?php

namespace Lza\App\Admin\Modules;


use Lza\LazyAdmin\Runtime\BaseView;

/**
 * Base View for Admin
 *
 * @var permission
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class AdminView extends BaseView
{
    use AdminViewTrait;

    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreate()
    {
        parent::onCreate();

        $this->uiOptions['config_dir'] = ADMIN_CONFIG_PATH;

        $this->data->header = 'public/Header.html';
        $this->data->sidebar = 'public/sidebar/Master.html';
        $this->data->footer = 'public/Footer.html';

        //$this->data->favicon = $this->setting->favicon;
        $this->data->logo = $this->setting->logo;
        $this->data->title = $this->getTitle();
        $this->data->module = $this->module;
        $this->data->modulePath = $this->modulePath;
        $this->data->regionPath = ROOT_FOLDER . 'lzaadmin';
        $this->data->view = $this->view;
        $this->data->menu = $this->doGetMenu();
        $this->data->fullname = $this->session->get('user.fullname');

        if (!$this->hasPermissionToAccess())
        {
            $this->setError('Module Not Found!');
            $this->setContentView('general/listall/Error.html');
            return;
        }

        $moduleClass = camel_case($this->env->module, true);
        $viewClass = camel_case($this->env->view, true);
        $modulePath = strtolower($moduleClass);
        $viewPath = strtolower($viewClass);

        $moduleFile = ADMIN_RES_PATH . "layouts/{$modulePath}/{$viewPath}/Master.html";
        $generalFile = ADMIN_RES_PATH . "layouts/general/{$viewPath}/Master.html";
        if (is_file(fpath($moduleFile)))
        {
            $this->setContentView("{$modulePath}/{$viewPath}/Master.html");
        }
        elseif ($this->module === 'setting')
        {
            $this->setContentView('setting/edit/Master.html');
        }
        elseif (is_file(fpath($generalFile)))
        {
            $this->setContentView("general/{$viewPath}/Master.html");
        }
        else
        {
            $this->isPageNotFound = true;
        }

        $isCollapse = $this->session->menuCollapse;
        $this->session->menuCollapse = $isCollapse;

        if (isset($this->request->action) && $this->request->action === 'logout')
        {
            $this->onBtnLogoutClick();
        }
    }

    /**
     * Event when CSSes is loading
     *
     * @throws
     */
    protected function onLoadStyles()
    {
        parent::onLoadStyles();

        $csses = [
            'vendors/components/bootstrap/css/bootstrap.min.css',
            'vendors/onokumus/metismenu/dist/metisMenu.min.css',
            'vendors/nostalgiaz/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css',
            'vendors/components/font-awesome/css/font-awesome.min.css',
            'vendors/iron-summit-media/startbootstrap-sb-admin-2/dist/css/sb-admin-2.css',
            'admin-res/styles/styles.css',
            'admin-res/styles/toggle_sidebar.css'
        ];
        foreach ($csses as $css)
        {
            $this->data->styles['master'][] = WEBSITE_ROOT . $css;
        }
    }

    /**
     * Event when Logout button is clicked
     *
     * @throws
     */
    public function onBtnLogoutClick()
    {
        $target = $this->session->get('user.is_admin') === 'Yes' ? 'navigateToAdminPanel' : 'navigateToHome';
        session_destroy();
        $this->preventDefault();
        $this->$target();
    }

    /**
     * Event when Check Unique Ajax is called
     *
     * @throws
     */
    protected function onCheckUnique()
    {
        $view = $this->env->view;

        $id = $view === 'add' ? null : (isset($this->env->id) ? $this->env->id : null);
        $field = isset($this->request->field) ? $this->request->field : null;
        $type = isset($this->request->fieldtype) ? $this->request->fieldtype : null;
        $value = isset($this->request->$field) ? $this->request->$field : null;

        echo $this->encryptor->jsonEncode(
            $this->validator->validateUnique(
                $this->module, $field, $type, $id, $value
            )
        );
        exit;
    }

    /**
     * @throws
     */
    public function getTitle()
    {
        $companyName = $this->setting->companyName;
        $title = strlen($companyName) > 0 ? "$companyName Admin | " : 'Admin | ';

        $table = $this->doGetTable($this->module);
        if ($table !== null)
        {
            $title = initcap($this->view) . ' ' . $table['single'];
        }
        else
        {
            $title .= initcap($this->module);
        }

        return $title;
    }

    /**
     * Event when Toggle Menu Ajax is called
     *
     * @throws
     */
    public function onToggleMenu()
    {
        $isCollapse = $this->session->menuCollapse;
        $this->session->menuCollapse = !($isCollapse !== null ? $isCollapse : false);
    }
}
