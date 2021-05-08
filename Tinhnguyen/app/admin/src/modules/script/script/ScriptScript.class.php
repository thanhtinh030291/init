<?php

namespace Lza\App\Admin\Modules\Script\Script;


use Lza\App\Admin\Modules\Script\Script;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ScriptScript extends Script
{
    /**
     * Load Presenter class
     *
     * @throws
     */
    protected function bindPresenter()
    {
        DIContainer::bindSingleton('presenter', ScriptScriptPresenter::class);
    }

    /**
     * Load View class
     *
     * @throws
     */
    protected function bindView()
    {
        DIContainer::resolve(ScriptScriptView::class)->show();
    }
}
