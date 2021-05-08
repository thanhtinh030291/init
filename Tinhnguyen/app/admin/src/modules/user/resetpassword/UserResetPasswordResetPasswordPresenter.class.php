<?php

namespace Lza\App\Admin\Modules\User\ResetPassword;


use Lza\Config\Models\ModelPool;

/**
 * Handle Reset Password action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserResetPasswordResetPasswordPresenter extends UserResetPasswordPresenter
{
    /**
     * Validate inputs and do Reset User Password request
     *
     * @throws
     */
    public function doResetPassword($email, $password, $confirm)
    {
        $model = ModelPool::getModel('lzauser');
        $user = $model->getUserByEmail($email);
        if (!$user)
        {
            $this->onError('Invalid Email!');
            return;
        }
        $this->resetPassword($this, $user, $password);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onPasswordResetedSuccess($data = null)
    {
        $this->viewer->navigateToLogin(false);
    }
}
