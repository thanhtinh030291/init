<?php

namespace Lza\App\Admin\Modules\Dashboard\Content;


use Lza\App\Admin\Modules\Dashboard\Dashboard;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class DashboardContent extends Dashboard
{
    /**
     * Load Presenter class
     *
     * @throws
     */
    protected function bindPresenter()
    {
        DIContainer::bindSingleton('presenter', DashboardContentPresenter::class);
    }

    /**
     * Load View class
     *
     * @throws
     */
    protected function bindView()
    {
        DIContainer::resolve(DashboardContentView::class)->show();
    }
}
