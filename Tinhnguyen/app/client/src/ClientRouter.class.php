<?php

namespace Lza\App\Client;


use Lza\App\Client\Modules\General\ClientGeneral;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Client Router
 * Routes the visitors to the right view
 *
 * @var env
 * @var request
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientRouter
{
    /**
     * Constants belong to the Front End only
     *
     * @throws
     */
    public function defineConstants()
    {
        define('ADMIN_PATH', DOCUMENT_ROOT . 'app/admin/');
        define('ADMIN_SRC_PATH', ADMIN_PATH . 'src/');
        define('ADMIN_RES_PATH', ADMIN_PATH . 'res/');
        define('ADMIN_MODULE_PATH', ADMIN_PATH . 'src/modules/');

        define('CLIENT_PATH', DOCUMENT_ROOT . 'app/client/');
        define('CLIENT_SRC_PATH', CLIENT_PATH . 'src/');
        define('CLIENT_RES_PATH', WEBSITE_ROOT . 'resources/');
        define('CLIENT_CONFIG_PATH', CLIENT_PATH . 'res/configs/');
        define('CLIENT_MODULE_PATH', CLIENT_PATH . 'src/modules/');
        define('RES_PATH', CLIENT_PATH . 'res/');

        // Custom Constants
        define('DIAG_SCORE', 0.5);
    }

    /**
     * Define which module/view will be loaded
     *
     * @throws
     */
    public function route($params)
    { 
        if (FORCE_CLIENT_SSL && (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on"))
        {
            header("Location: https://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}");
            exit();
        }
        
        $this->env->region = 'client';
        $this->env->module = 'client';
        $this->env->view = strlen($params[0]) > 0 ? snake_case($params[0]) : 'home';
        $this->env->action = snake_case($this->request->action);
        $this->env->child1 = isset($params[1]) ? snake_case($params[1]) : null;
        $this->env->child2 = isset($params[2]) ? snake_case($params[2]) : null;
        $this->env->child3 = isset($params[3]) ? snake_case($params[3]) : null;
        $this->env->child4 = isset($params[4]) ? snake_case($params[4]) : null;
        $this->env->child5 = isset($params[5]) ? snake_case($params[5]) : null;
    }

    /**
     * Begin to load module/view
     *
     * @throws
     */
    public function launch()
    { 
        $moduleClass = camel_case($this->env->module, true);
        $viewClass = camel_case($this->env->view, true);
        $viewPath = strtolower($viewClass);
        $class = ClientGeneral::class;
        
        $path = CLIENT_MODULE_PATH . "{$viewPath}/{$moduleClass}{$viewClass}.class.php";
        if (is_file(fpath($path)))
        {
            $class = "Lza\\App\\Client\\Modules\\{$viewClass}\\{$moduleClass}{$viewClass}";
        }

        $launcher = DIContainer::resolve($class);
        $launcher->launch();
    }
}
