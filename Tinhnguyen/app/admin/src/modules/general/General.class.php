<?php

namespace Lza\App\Admin\Modules\General;


use Lza\App\Admin\Modules\Admin;
use Lza\App\Admin\Modules\AdminPresenter;
use Lza\App\Admin\Modules\AdminView;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class General extends Admin
{
    /**
     * Load Presenter class
     *
     * @throws
     */
    protected function bindPresenter()
    {
        $viewClass = camel_case($this->env->view, true);
        $actionClass = camel_case($this->env->action, true);
        $viewPath = strtolower($viewClass);

        $class = AdminPresenter::class;
        if (
            is_file(
                fpath(
                    ADMIN_MODULE_PATH . "/general/{$viewPath}/{$viewClass}{$actionClass}Presenter.class.php"
                )
            )
        )
        {
            $class = "Lza\\App\\Admin\\Modules\\General\\{$viewClass}\\{$viewClass}{$actionClass}Presenter";
        }
        elseif (
            is_file(
                fpath(
                    ADMIN_MODULE_PATH . "/general/{$viewPath}/{$viewClass}Presenter.class.php"
                )
            )
        )
        {
            $class = "Lza\\App\\Admin\\Modules\\General\\{$viewClass}\\{$viewClass}Presenter";
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
        $viewClass = camel_case($this->env->view, true);
        $viewPath = strtolower($viewClass);

        $class = AdminView::class;
        if (
            is_file(
                fpath(
                    ADMIN_MODULE_PATH . "/general/{$viewPath}/{$viewClass}View.class.php"
                )
            )
        )
        {
            $class = "Lza\\App\\Admin\\Modules\\General\\{$viewClass}\\{$viewClass}View";
        }

        DIContainer::resolve($class)->show();
    }
}
