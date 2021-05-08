<?php

namespace Lza\App\Admin\Modules\User\ResetPassword;


use Lza\Config\Models\ModelPool;
use Lza\App\Admin\Modules\AdminPresenter;

/**
 * Handle Default action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserResetPasswordPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Check User Reset Password Token request
     *
     * @throws
     */
    public function doCheckToken($token)
    {
        $model = ModelPool::getModel('UserResetPassword');
        $emails = $model->where('token = ? andvexpire > NOW()', $token);
        $email = $emails->select('email')->fetch();
        if (!$email)
        {
            $this->viewer->navigateToLogin(false);
        }
        return $email['email'];
    }
}
