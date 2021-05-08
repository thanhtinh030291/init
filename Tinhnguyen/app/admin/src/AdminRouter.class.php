<?php

namespace Lza\App\Admin;


use Lza\App\Admin\Modules\General\General;
use Lza\App\Admin\Modules\Setting\Edit\SettingEdit;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Admin Router
 * Routes the visitors to the right view
 **
 * @var env
 * @var request
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class AdminRouter
{
    /**
     * @throws
     */
    public function defineConstants()
    {
        define('ADMIN_PATH', DOCUMENT_ROOT . 'app/admin/');
        define('ADMIN_SRC_PATH', ADMIN_PATH . 'src/');
        define('ADMIN_RES_PATH', ADMIN_PATH . 'res/');
        define('ADMIN_CONFIG_PATH', ADMIN_PATH . 'res/configs/');
        define('ADMIN_MODULE_PATH', ADMIN_PATH . 'src/modules/');

        define('LIST_LEVEL', 1);
        define('SHOW_LEVEL', 2);
        define('ADD_LEVEL', 4);
        define('EDIT_LEVEL', 8);
        define('RES_PATH', ADMIN_RES_PATH);
    }

    /**
     * @throws
     */
    public function route($params)
    {
        $host = $_SERVER["HTTP_HOST"];
        $uri = $_SERVER["REQUEST_URI"];

        if (FORCE_ADMIN_SSL && (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on"))
        {
            header("Location: https://{$host}{$uri}");
            exit();
        }

        $this->env->region = 'lzaadmin';
        $this->env->action = snake_case($this->request->action);

        if (!isset($params[1]))
        {
            $this->env->module = 'dashboard';
            $this->env->view = 'content';
            $this->env->level = 1;
            return;
        }

        switch ($params[1])
        {
            case 'js':
                $this->env->module = 'script';
                $this->env->view = 'script';
                $this->env->jsmodule = snake_case($params[2]);
                $this->env->jsview = snake_case($params[3]);
                break;
            case 'login':
                $this->env->module = 'user';
                $this->env->view = 'login';
                break;
            case 'logout':
                $this->env->module = 'user';
                $this->env->view = 'login';
                $this->env->action = 'logout';
                break;
            case 'change-password':
                $this->env->module = 'user';
                $this->env->view = 'change_password';
                break;
            case 'forget-password':
                $this->env->module = 'user';
                $this->env->view = 'forget_password';
                break;
            case 'reset-password':
                $this->env->module = 'user';
                $this->env->view = 'reset_password';
                $this->env->child1 = isset($params[2]) ? snake_case($params[2]) : null;
                break;
            case 'dashboard':
                $this->env->module = 'dashboard';
                $this->env->view = 'content';
                $this->env->child1 = isset($params[2]) ? snake_case($params[2]) : null;
                $this->env->child2 = isset($params[3]) ? snake_case($params[3]) : null;
                break;
            case 'setting':
                $this->env->module = 'setting';
                $this->env->view = isset($params[2]) ? snake_case($params[2]) : 'setting_smtp';
                break;
            default:
                $this->env->module = snake_case($params[1]);
                if (!isset($params[2]))
                {
                    header(str_replace('//', '/', "location: {$_SERVER['REQUEST_URI']}/list"));
                    exit;
                }
                $this->env->view = $params[2];
                switch ($params[2])
                {
                    case '':
                    case 'list':
                        $this->env->view = 'listall';
                        $this->env->level = 1;
                        if (!isset($params[3]))
                        {
                            break;
                        }
                        switch ($params[3])
                        {
                            case 'showall':
                                $this->env->action = snake_case($params[3]);
                                break;
                            case 'namelist':
                                $this->env->action = snake_case($params[3]);
                                break;
                            default:
                                $this->env->page = $params[3];
                                $this->env->condition = isset($params[4]) ? snake_case($params[4]) : null;
                        }
                        break;
                    case 'tree':
                        $this->env->view = 'tree';
                        $this->env->level = 1;
                        if (!isset($params[3]))
                        {
                            break;
                        }
                        switch ($params[3])
                        {
                            case 'showall':
                                $this->env->action = snake_case($params[3]);
                                break;
                        }
                        break;
                    case 'list-ajax':
                        $this->env->view = 'listall';
                        $this->env->child1 = 'ajax';
                        $this->env->level = 1;
                        break;
                    case 'calendar':
                        $this->env->id = isset($params[3]) ? $params[3] : 1;
                        $this->env->level = 0;
                        break;
                    case 'add':
                        $this->env->ref = isset($params[3]) ? snake_case($params[3]) : null;
                        $this->env->id = isset($params[4]) ? $params[4] : 1;
                        $this->env->level = 4;
                        break;
                    case 'edit':
                        $this->env->id = isset($params[3]) ? $params[3] : 1;
                        $this->env->level = 8;
                        break;
                    case 'show':
                        $this->env->id = isset($params[3]) ? $params[3] : 1;
                        $this->env->level = 2;
                        break;
                    case 'statistics':
                        $this->env->level = 0;
                        break;
                }
        }
    }

    /**
     * @throws
     */
    public function launch()
    {
        $moduleClass = camel_case($this->env->module, true);
        $viewClass = camel_case($this->env->view, true);
        $modulePath = strtolower($moduleClass);
        $viewPath = strtolower($viewClass);

        $class = General::class;
        $moduleFile = ADMIN_MODULE_PATH . "/{$modulePath}/{$viewPath}/{$moduleClass}{$viewClass}.class.php";
        $generalFile = ADMIN_MODULE_PATH . "/general/{$viewPath}/{$moduleClass}{$viewClass}.class.php";
        if (is_file(fpath($moduleFile)))
        {
            $class = "Lza\\App\\Admin\\Modules\\{$moduleClass}\\{$viewClass}\\{$moduleClass}{$viewClass}";
        }
        elseif (is_file(fpath($generalFile)))
        {
            $class = "Lza\\App\\Admin\\Modules\\General\\{$viewClass}\\{$moduleClass}{$viewClass}";
        }
        elseif ($this->env->module === 'setting')
        {
            $class = SettingEdit::class;
        }

        DIContainer::bindSingleton('permission', Permission::class);
        $launcher = DIContainer::resolve($class);
        $launcher->launch();
    }
}
