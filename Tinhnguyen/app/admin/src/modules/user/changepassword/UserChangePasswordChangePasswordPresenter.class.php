<?php

namespace Lza\App\Admin\Modules\User\ChangePassword;


use Lza\Config\Models\ModelPool;
use Lza\App\Admin\Modules\User\Login\UserLoginPresenter;

/**
 * Handle Change Password action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserChangePasswordChangePasswordPresenter extends UserLoginPresenter
{
    /**
     * Validate inputs and do Change Password request
     *
     * @throws
     */
    public function doValidateData($email, $oldpass, $newpass, $confirm)
    {
        $model = ModelPool::getModel('lzauser');
        $oldpass = $this->encryptor->hash($oldpass, 2);
        $users = $model->where("email = ? and password = ?", $email, $oldpass);
        $user = $users->fetch();
        if (!$user)
        {
            $this->onError($this->i18n->oldpassNotMatchMessage);
            return;
        }

        $this->changePassword($this, $user, $newpass);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onPasswordChangedSuccess($data = null)
    {
        $this->viewer->onBtnLogoutClick();
    }
}
