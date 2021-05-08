<?php

namespace Lza\App\Client\Modules\Api\V10\Provider;


use Lza\App\Client\Modules\Api\V10\ClientApiV10Presenter;

/**
 * Handle Get Provider Options action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiProviderGetOptionsPresenter extends ClientApiV10Presenter
{
    /**
     * Get Providers request
     *
     * @throws
     */
    public function doGetProviderOptions()
    {
        $sql = $this->sql->pcvProvider([
            'where' => ''
        ]);
        $providers = $this->sql->query($sql, [], 'main_website');
        if (count($providers) === 0)
        {
            return false;
        }
        return $providers;
    }
}
