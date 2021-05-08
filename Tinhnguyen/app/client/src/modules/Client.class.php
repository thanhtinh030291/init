<?php

namespace Lza\App\CLient\Modules;


use Lza\App\Client\Modules\ClientController;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\HttpRequestHandler;

/**
 * Base Launcher for Front End
 *
 * @var action
 * @var env
 * @var module
 * @var view
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Client
{
    /**
     * Load the classes
     *
     * @throws
     */
    public function launch()
    {
        $this->bindController();
        $this->bindPresenter();
        $this->bindView();
    }

    /**
     * Load Controller class
     *
     * @throws
     */
    protected function bindController()
    {
        DIContainer::bindSingleton('httpRequestHandler', HttpRequestHandler::class);
        DIContainer::bindSingleton('controller', ClientController::class);
    }

    /**
     * Load Presenter class
     *
     * @throws
     */
    protected function bindPresenter()
    {
        $moduleClass = camel_case($this->env->module, true);
        $viewClass = camel_case($this->env->view, true);
        $actionClass = camel_case($this->env->action, true);
        $viewPath = strtolower($viewClass);

        $class = "Lza\\App\\Client\\Modules\\{$viewClass}\\{$moduleClass}{$viewClass}Presenter";
        if (
            is_file(
                fpath(
                    CLIENT_MODULE_PATH . "/{$viewPath}/{$moduleClass}{$viewClass}{$actionClass}Presenter.class.php"
                )
            )
        )
        {
            $class = "Lza\\App\\Client\\Modules\\{$viewClass}\\{$moduleClass}{$viewClass}{$actionClass}Presenter";
        }

        DIContainer::bindSingleton('presenter', $class);
    }

    /**
     * Load View class
     *
     * @throws
     */
    protected function bindView()
    {
        $moduleClass = camel_case($this->env->module, true);
        $viewClass = camel_case($this->env->view, true);

        $class = "Lza\\App\\Client\\Modules\\{$viewClass}\\{$moduleClass}{$viewClass}View";
        DIContainer::resolve($class)->show();
    }
}
