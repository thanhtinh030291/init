<?php

namespace Lza\App\Admin\Modules;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * @var action
 * @var env
 * @var inflector
 * @var module
 * @var view
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Admin
{
    /**
     * @throws
     */
    public function launch()
    {
        $this->bindModel();
        $this->bindController();
        $this->bindPresenter();
        $this->bindView();
    }

    /**
     * @throws
     */
    protected function bindModel()
    {
        DIContainer::bindValue('model', ModelPool::getModel($this->module));
    }

    /**
     * Load Controller class
     *
     * @throws
     */
    protected function bindController()
    {
        $moduleClass = camel_case($this->env->module, true);
        $modulePath = strtolower($moduleClass);

        $class = AdminController::class;
        $controllerName = $this->inflector->pluralize($moduleClass) . "Controller";
        if (is_file(fpath(ADMIN_MODULE_PATH . "/{$modulePath}/{$controllerName}.class.php")))
        {
            $class = "Lza\\App\\Admin\\Modules\\{$moduleClass}\\{$controllerName}";
        }

        DIContainer::bindSingleton('controller', $class);
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
        $modulePath = strtolower($moduleClass);
        $viewPath = strtolower($viewClass);

        $actionPresenter = "{$moduleClass}{$viewClass}{$actionClass}Presenter";
        $viewPresenter = "{$moduleClass}{$viewClass}Presenter";
        $modulePresenter = "{$moduleClass}Presenter";

        $class = AdminPresenter::class;
        if (is_file(fpath(ADMIN_MODULE_PATH . "/{$modulePath}/{$viewPath}/{$actionPresenter}.class.php")))
        {
            $class = "Lza\\App\\Admin\\Modules\\{$moduleClass}\\{$viewClass}\\{$actionPresenter}";
        }
        elseif (is_file(fpath(ADMIN_MODULE_PATH . "/{$modulePath}/{$viewPath}/{$viewPresenter}.class.php")))
        {
            $class = "Lza\\App\\Admin\\Modules\\{$moduleClass}\\{$viewClass}\\{$viewPresenter}";
        }
        elseif (is_file(fpath(ADMIN_MODULE_PATH . "/{$modulePath}/$modulePresenter.class.php")))
        {
            $class = "Lza\\App\\Admin\\Modules\\{$moduleClass}\\$modulePresenter";
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
        $modulePath = strtolower($moduleClass);
        $viewPath = strtolower($viewClass);

        $viewView = "{$moduleClass}{$viewClass}View";
        $moduleView = "{$moduleClass}View";

        $class = AdminView::class;
        if (is_file(fpath(ADMIN_MODULE_PATH . "/{$modulePath}/{$viewPath}/{$viewView}.class.php")))
        {
            $class = "Lza\\App\\Admin\\Modules\\{$moduleClass}\\{$viewClass}\\{$viewView}";
        }
        elseif (is_file(fpath(ADMIN_MODULE_PATH . "/{$modulePath}/{$moduleView}.class.php")))
        {
            $class = "Lza\\App\\Admin\\Modules\\{$moduleClass}\\{$moduleView}";
        }

        DIContainer::resolve($class)->show();
    }
}
