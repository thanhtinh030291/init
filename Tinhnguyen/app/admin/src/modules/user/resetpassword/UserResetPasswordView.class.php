<?php

namespace Lza\App\Admin\Modules\User\ResetPassword;


use Lza\App\Admin\Modules\AdminView;

/**
 * Process User Reset Password page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserResetPasswordView extends AdminView
{
    /**
     * @var string Email of the User
     */
    private $email;

    /**
     * Event when the page is creating
     *
     * @throws
     */
    protected function onCreate()
    {
        parent::onCreate();
        $this->email = $this->doCheckToken($this->env->child1);
    }

    /**
     * Event when Reset Password button is clicked
     *
     * @throws
     */
    public function onBtnResetPasswordClick()
    {
        $password = $this->request->post->password;
        $confirm = $this->request->post->confirm;
        $errors = $this->validator->validatePassword($password, $confirm, null, true);
        if (count($errors) > 0)
        {
            $this->onError(implode('<br />', $errors));
            return;
        }

        $this->doResetPassword($this->email, $password, $confirm);
    }

    /**
     * Is this page requires login?
     *
     * @throws
     */
    protected function isLoginRequired()
    {
        return false;
    }

    /**
     * @throws
     */
    protected function hasPermissionToAccess()
    {
        return true;
    }

    /**
     * Event when Javascript is loading
     *
     * @throws
     */
    protected function onLoadScripts()
    {
        parent::onLoadScripts();
        $this->data->scripts['body'][] = WEBSITE_ROOT
                . 'admin-res/scripts/user/resetpassword/resetpassword.js';
    }
}
