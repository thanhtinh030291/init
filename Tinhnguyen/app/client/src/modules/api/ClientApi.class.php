<?php

namespace Lza\App\Client\Modules\Api;


use Lza\App\Client\Modules\Client;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\FirebaseCloudMessageHandler;
use Lza\LazyAdmin\Utility\Tool\FptSmsHandler;
use Lza\LazyAdmin\Utility\Tool\HttpRequestHandler;
use Lza\LazyAdmin\Utility\Tool\SlimHandler;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApi extends Client
{
    /**
     * Load Controller class
     *
     * @throws
     */
    protected function bindController()
    {
        $versionClass = camel_case($this->env->child1, true);
        $moduleClass = camel_case($this->env->child2, true);
        $class = "Lza\\App\\Client\\Modules\\Api\\{$versionClass}\\{$moduleClass}\\ClientApi{$moduleClass}Controller";
        DIContainer::bindSingleton('httpRequestHandler', HttpRequestHandler::class);
        DIContainer::bindSingleton('controller', $class);
    }

    /**
     * Load Presenter class
     *
     * @throws
     */
    protected function bindPresenter()
    {
        $versionClass = camel_case($this->env->child1, true);
        $moduleClass = camel_case($this->env->child2, true);
        $actionClass = camel_case($this->env->child3, true);
        $methodClass = camel_case($_SERVER['REQUEST_METHOD'], true);

        $class = "Lza\\App\\Client\\Modules\\Api\\{$versionClass}\\{$moduleClass}\\ClientApi{$moduleClass}{$methodClass}{$actionClass}Presenter";
        DIContainer::bindSingleton('smsHandler', FptSmsHandler::class);
        DIContainer::bindSingleton('noteHandler', FirebaseCloudMessageHandler::class);
        DIContainer::bindSingleton('presenter', $class);
    }

    /**
     * Load View class
     *
     * @throws
     */
    protected function bindView()
    {
        $versionClass = camel_case($this->env->child1, true);
        $moduleClass = camel_case($this->env->child2, true);

        $class = "Lza\\App\\Client\\Modules\\Api\\{$versionClass}\\{$moduleClass}\\ClientApi{$moduleClass}View";
        DIContainer::bindSingleton('apiHandler', SlimHandler::class);
        DIContainer::resolve($class)->show();
    }
}
