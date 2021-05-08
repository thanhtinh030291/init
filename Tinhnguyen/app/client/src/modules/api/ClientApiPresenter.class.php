<?php

namespace Lza\App\Client\Modules\Api;


use Lza\App\Client\Modules\ClientPresenter;
use Lza\Config\Models\ModelPool;

/**
 * Default Presenter for API
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiPresenter extends ClientPresenter
{
    /**
     * @throws
     */
    public function isTokenValid($token)
    {
        $model = ModelPool::getModel('MobileUserSession', 'main');
        $sessions = $model->where('token', $token);
        return $sessions->fetch();
    }

    /**
     * @throws
     */
    public function isOtpValid($token)
    {
        $model = ModelPool::getModel('MobileClaimOtp', 'main');
        $otps = $model->where('otp = ? and expire >= NOW()', $token);
        $otp = $otps->fetch();

        if ($otp !== false)
        {
            $otp->delete();
            return true;
        }
        else
        {
            return false;
        }
    }
}
