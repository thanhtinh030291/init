<?php

namespace Lza\App\Admin\Modules\Script\Script;


use Lza\App\Admin\Modules\AdminPresenter;

/**
 * Handle Get Javascript Contents action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ScriptScriptPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Get Javascript Contents request
     *
     * @throws
     */
    public function doGetScript($jsmodule, $jsview)
    {
        $region = $this->env->region;
        $js = $this->session->get("js.{$region}.{$jsmodule}.{$jsview}");
        $js = implode("\n", $js !== null  ? $js : ['']);
        echo htmlspecialchars_decode("$(document).ready(function() {\n{$js}\n});");
    }
}
