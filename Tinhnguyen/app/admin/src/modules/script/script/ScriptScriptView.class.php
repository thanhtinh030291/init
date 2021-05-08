<?php

namespace Lza\App\Admin\Modules\Script\Script;


use Lza\App\Admin\Modules\AdminView;

/**
 * Process Javascript page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ScriptScriptView extends AdminView
{
    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreate()
    {
        parent::onCreate();
        header('Content-type: text/javascript');
        $this->doGetScript($this->env->jsmodule, $this->env->jsview);
        exit();
    }
}
