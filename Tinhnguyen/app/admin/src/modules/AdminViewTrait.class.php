<?php

namespace Lza\App\Admin\Modules;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait AdminViewTrait
{
    /**
     * @var string Error message
     */
    protected $error;

    /**
     * @throws
     */
    protected function hasPermissionToAccess()
    {
        return $this->session->get('user.is_admin') === 'Yes';
    }

    /**
     * Event when Javascript is loading
     *
     * @throws
     */
    protected function onLoadScripts()
    {
        parent::onLoadScripts();

        $vendor = WEBSITE_ROOT . 'vendors';
        $lib = WEBSITE_ROOT . 'libraries';
        $libs = [
            "{$vendor}/components/jquery/jquery.min.js",
            "{$vendor}/components/bootstrap/js/bootstrap.min.js",
            "{$vendor}/onokumus/metismenu/dist/metisMenu.min.js",
            "{$vendor}/nostalgiaz/bootstrap-switch/dist/js/bootstrap-switch.min.js",
            "{$vendor}/iron-summit-media/startbootstrap-sb-admin-2/dist/js/sb-admin-2.js",
            "{$lib}/jqueryvalidation/JqueryValidation/jquery.validate.min.js",
            "{$lib}/datetimepicker/DatetimePicker/build/jquery.datetimepicker.full.js",
            "{$lib}/fckeditor/ckeditor/ckeditor.js",
            "{$lib}/fckeditor/ckfinder/ckfinder.js",
            WEBSITE_ROOT . 'admin-res/scripts/master.js'
        ];
        foreach ($libs as $lib)
        {
            $this->data->scripts['master'][] = $lib;
        }

        $region = $this->env->region;
        $module = $this->env->module;
        $view = $this->env->view;

        $js = $this->session->get("js.{$region}.{$module}.{$view}");
        $this->session->set("js.{$region}.{$module}.{$view}", $js !== null ? $js : []);

        $this->data->scripts['master'][] = WEBSITE_ROOT . "{$region}/js/{$module}/{$view}";
    }

    /**
     * @throws
     */
    protected function isLoginRequired()
    {
        return true;
    }

    /**
     * @throws
     */
    protected function isAdminRequired()
    {
        return false;
    }

    /**
     * Event when Change Language button is clicked
     *
     * @throws
     */
    public function onBtnChangeLanguageClick()
    {
        $this->session->lzalanguage = $this->request->language;
        header("Refresh:0");
        exit;
    }

    /**
     * Go to Module Default page
     *
     * @throws
     */
    public function navigateToModule()
    {
        header("location: " . WEBSITE_ROOT . "{$this->modulePath}/list");
        exit();
    }

    /**
     * Go to Module List page
     *
     * @throws
     */
    public function navigateToList($page, $condition)
    {
        header("location: " . WEBSITE_ROOT . "{$this->modulePath}/list/{$page}/{$condition}");
        exit();
    }

    /**
     * Go to Dispaly Item Details page
     *
     * @throws
     */
    public function navigateToShowItem($id)
    {
        header("location: " . WEBSITE_ROOT . "{$this->modulePath}/show/{$id}");
        exit();
    }

    /**
     * Go to Edit Item page
     *
     * @throws
     */
    public function navigateToEditItem($id)
    {
        header("location: " . WEBSITE_ROOT . "{$this->modulePath}/edit/{$id}");
        exit();
    }

    /**
     * Go to Add Item page
     *
     * @throws
     */
    public function navigateToAddItem()
    {
        header("location: " . WEBSITE_ROOT . "{$this->modulePath}/add");
        exit();
    }

    /**
     * Go to Login page
     *
     * @throws
     */
    public function navigateToLogin($return = true)
    {
        $returnUri = $return ? "?return_url=" . urlencode($_SERVER['REQUEST_URI']) : '';
        header("location: " . WEBSITE_ROOT . "lzaadmin/login" . $returnUri);
        exit();
    }

    /**
     * @throws
     */
    public function reload()
    {
        header("Refresh:0");
        exit();
    }

    /**
     * @throws
     */
    public function setError($message)
    {
        $this->data->errorAlert .= "<br />{$message}";
        $this->error .= "<br />{$message}";
    }
}
