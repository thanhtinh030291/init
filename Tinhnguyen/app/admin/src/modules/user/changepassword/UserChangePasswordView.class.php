<?php

namespace Lza\App\Admin\Modules\User\ChangePassword;


use Lza\App\Admin\Modules\AdminView;

/**
 * Process User Change Password page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserChangePasswordView extends AdminView
{
    /**
     * Event when Change Password button is clicked
     *
     * @throws
     */
    public function onBtnChangePasswordClick()
    {
        $old = $this->request->post->oldpass;
        $new = $this->request->post->newpass;
        $confirm = $this->request->post->confirm;
        $email = $this->session->get('user.email');

        $errors = $this->validator->validatePassword($new, $confirm, $old, true);
        if (count($errors) > 0)
        {
            $this->onError(implode('<br />', $errors));
            return;
        }

        $this->doValidateData($email, $old, $new, $confirm);
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
                . 'admin-res/scripts/user/changepassword/changepassword.js';
    }
}
