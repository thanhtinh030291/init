<?php

namespace Lza\App\Admin\Modules\User\Login;


use Lza\App\Admin\Modules\AdminView;

/**
 * Process User Login page
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserLoginView extends AdminView
{
    /**
     * Event when Login button is clicked
     *
     * @throws
     */
    public function onBtnLoginClick()
    {
        $this->doValidateCredentials($this->request->post->username, $this->request->post->password);
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
}
