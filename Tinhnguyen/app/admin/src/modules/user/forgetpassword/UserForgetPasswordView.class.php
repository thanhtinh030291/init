<?php

namespace Lza\App\Admin\Modules\User\ForgetPassword;


use Lza\App\Admin\Modules\AdminView;

/**
 * Process User Forget Password page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserForgetPasswordView extends AdminView
{
    /**
     * Event when Forget Password button is clicked
     *
     * @throws
     */
    public function onBtnForgetPasswordClick()
    {
        $this->doForgetPassword($this->request->post->email);
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
    protected function isAdminRequired()
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
                . 'admin-res/scripts/user/forgetpassword/forgetpassword.js';
    }
}
