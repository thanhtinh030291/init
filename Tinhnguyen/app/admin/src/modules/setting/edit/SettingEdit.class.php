<?php

namespace Lza\App\Admin\Modules\Setting\Edit;


use Lza\App\Admin\Modules\Setting\Setting;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SettingEdit extends Setting
{
    /**
     * @throws
     */
    protected function bindModel()
    {
        DIContainer::bindValue('model', ModelPool::getModel('lzasetting'));
    }

    /**
     * Load Presenter class
     *
     * @throws
     */
    protected function bindPresenter()
    {
        DIContainer::bindSingleton('presenter', SettingEditPresenter::class);
    }

    /**
     * Load View class
     *
     * @throws
     */
    protected function bindView()
    {
        DIContainer::resolve(SettingEditView::class)->show();
    }
}
