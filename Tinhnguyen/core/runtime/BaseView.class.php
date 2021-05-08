<?php

namespace Lza\LazyAdmin\Runtime;


use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Base View
 * Abstract Class for any View in the Application
 * Has the responsibility to handle requests and display webpages
 *
 * @var action
 * @var csrf
 * @var datetime
 * @var encryptor
 * @var env
 * @var i18n
 * @var layoutHandler
 * @var logger
 * @var module
 * @var presenter
 * @var region
 * @var request
 * @var session
 * @var setting
 * @var validator
 * @var view
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
abstract class BaseView
{
    /**
     * @var string Module's Path
     */
    protected $modulePath;

    /**
     * @var string View's Content Path
     */
    protected $contentView;

    /**
     * @var array View's Options
     */
    protected $uiOptions = [];

    /**
     * @var array Classes to be used in the layouts
     */
    protected $uiClasses = [];

    /**
     * @var object Data to be used in the layouts
     */
    protected $data;

    /**
     * @var boolean Is default Form's handler prevented
     */
    protected $isPreventDefault = false;

    /**
     * @var boolean Is Requested page not found
     */
    protected $isPageNotFound = false;

    /**
     * @throws
     */
    public function __construct()
    {
        if (
            $this->isLoginRequired() &&
            (
                empty($this->session->get('user.username')) ||
                empty($this->session->get('user.role'))
            )
        )
        {
            $this->navigateToLogin();
        }

        $isAdmin = $this->session->get('user.is_admin');
        if ($this->isAdminRequired() && $isAdmin !== null && $isAdmin !== 'Yes')
        {
            $this->navigateToHome();
        }

        if ($this->session->language === null)
        {
            $this->session->language = '';
        }

        if ($this->session->lzalanguage === null)
        {
            $this->session->lzalanguage = '';
        }

        $this->session->rootFolder = ROOT_FOLDER;
        $this->session->actualLink = WEBSITE_URL;

        $this->session->alertDebug = $this->session->alertDebug !== null
                ? $this->session->alertDebug : [];
        $this->session->alertInfo = $this->session->alertInfo !== null
                ? $this->session->alertInfo : [];
        $this->session->alertSuccess = $this->session->alertSuccess !== null
                ? $this->session->alertSuccess : [];
        $this->session->alertWarning = $this->session->alertWarning !== null
                ? $this->session->alertWarning : [];
        $this->session->alertError = $this->session->alertError !== null
                ? $this->session->alertError : [];
    }

    /**
     * @throws
     */
    public function show()
    {
        $this->onCreate();
        $this->onEventHandle();
        $this->onLoadStyles();
        $this->onLoadScripts();
        $this->onDisplay();
    }

    /**
     * @throws
     */
    protected function onCreate()
    {
        $this->modulePath = str_replace(
            '//', '/',
            chain_case($this->region) . '/' . chain_case($this->module)
        );

        $this->presenter->setViewer($this);

        $this->data = $this->presenter->getData();
        $this->data->res = (object) [
            'env' => $this->env,
            'encryptor' => $this->encryptor,
            'i18n' => $this->i18n,
            'setting' => $this->setting
        ];

        $this->data->styles = [
            'body' => [],
            'master' => []
        ];
        $this->data->scripts = [
            'body' => [],
            'master' => []
        ];
        $this->data->database = $this->database = DatabasePool::getDatabase();

        $this->data->tokenName = 'form_security';
        $this->data->tokenValue = $this->csrf->generate($this->data->tokenName);
    }

    /**
     * @throws
     */
    protected function onEventHandle()
    {
        if ($this->isPreventDefault)
        {
            $this->csrf->purge($this->data->tokenName);
            $this->csrf->purge($this->session->tokenName);
            return;
        }

        if (strlen($this->action) > 0)
        {
            $result = $this->csrf->validate($this->data->tokenName);
            if (!$result && !$this->csrf->validate($this->session->tokenName))
            {
                $this->csrf->purge($this->data->tokenName);
                $this->csrf->purge($this->session->tokenName);
                $this->session->add('alert_error', 'Invalid Security Token');
                $this->onNoActionActive();
                return;
            }

            $callback = 'onBtn' . camel_case($this->action, true) . 'Click';
            if ($this->isAjax())
            {
                $callback = 'on' . camel_case($this->action, true);
            }
            $this->$callback();

            if ($result)
            {
                $this->csrf->purge($this->data->tokenName);
            }
        }
        else
        {
            $this->csrf->purge($this->data->tokenName);
            $this->onNoActionActive();
        }
    }

    /**
     * @throws
     */
    protected function getTitle()
    {
        return initcap("{$this->module}_{$this->view}");
    }

    /**
     * @throws
     */
    protected function isLoginRequired()
    {
        return false;
    }

    /**
     * @throws
     */
    protected function isAdminRequired()
    {
        return false;
    }

    /**
     * @throws
     */
    public function preventDefault()
    {
        $this->isPreventDefault = true;
    }

    /**
     * @throws
     */
    protected function onNoActionActive()
    {
        // TODO: Override if neeeded
    }

    /**
     * @throws
     */
    protected function onLoadStyles()
    {

    }

    /**
     * @throws
     */
    protected function onLoadScripts()
    {

    }

    /**
     * @throws
     */
    protected function onBtnChangeLanguageClick()
    {
        $this->session->language = $this->request->language;
    }

    /**
     * @throws
     */
    public function onDisplay()
    {
        $this->displayAlerts();

        $this->layoutHandler->setOptions($this->uiOptions);
        $this->layoutHandler->setClasses($this->uiClasses);
        $this->layoutHandler->useCaching($this->useCaching());
        $this->layoutHandler->minify($this->minify());
        $this->layoutHandler->setData($this->data);
        $this->layoutHandler->display($this->isPageNotFound ? 'public/PageNotFound.html' : $this->contentView);
    }

    /**
     * @throws
     */
    private function displayAlerts()
    {
        if ($this->session->alertDebug !== null)
        {
            foreach (array_unique($this->session->alertDebug) as $item)
            {
                $this->data->debugAlert .= "<br/>{$item}";
            }
            unset($this->session->alertDebug);
        }

        if ($this->session->alertInfo !== null)
        {
            foreach (array_unique($this->session->alertInfo) as $item)
            {
                $this->data->infoAlert .= "<br/>{$item}";
            }
            unset($this->session->alertInfo);
        }

        if ($this->session->alertSuccess !== null)
        {
            foreach (array_unique($this->session->alertSuccess) as $item)
            {
                $this->data->successAlert .= "<br/>{$item}";
            }
            unset($this->session->alertSuccess);
        }

        if ($this->session->alertWarning !== null)
        {
            foreach (array_unique($this->session->alertWarning) as $item)
            {
                $this->data->warningAlert .= "<br/>{$item}";
            }
            unset($this->session->alertWarning);
        }

        if ($this->session->alertError !== null)
        {
            foreach (array_unique($this->session->alertError) as $item)
            {
                $this->data->errorAlert .= "<br/>{$item}";
            }
            unset($this->session->alertError);
        }
    }

    /**
     * @throws
     */
    public function getModulePath()
    {
        return $this->modulePath;
    }

    /**
     * @throws
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @throws
     */
    protected function useCaching()
    {
        return false;
    }

    /**
     * @throws
     */
    protected function minify()
    {
        return false;
    }

    public abstract function navigateToLogin($return = true);

    /**
     * @throws
     */
    public function navigateToHome()
    {
        header("location: " . WEBSITE_ROOT);
        echo ' ';
        exit();
    }

    /**
     * @throws
     */
    public function navigateToAdminPanel()
    {
        header("location: " . WEBSITE_ROOT . "lzaadmin");
        echo ' ';
        exit();
    }

    /**
     * @throws
     */
    public function navigateTo($location)
    {
        header("location: {$location}");
        echo ' ';
        exit();
    }

    /**
     * @throws
     */
    public function refreshPage()
    {
        header('Refresh:0');
        echo ' ';
        exit();
    }

    /**
     * @throws
     */
    public function setContentView($contentView = null)
    {
        $this->contentView = $contentView;
    }

    /**
     * @throws
     */
    public function __call($method, $params)
    {
        $vars = DIContainer::getMethodVars(get_class($this->presenter), $method);
        return call_user_func_array([$this->presenter, $method], array_merge($vars, $params));
    }

    /**
     * @throws
     */
    public function isAjax()
    {
        if (!empty($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] === true)
        {
            return true;
        }
        elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH']))
        {
            return true;
        }
        return false;
    }
}
