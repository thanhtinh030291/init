<?php

namespace Lza\App\Admin\Modules\User\Login;


use Lza\Config\Models\ModelPool;

/**
 * Handle Login action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserLoginLoginPresenter extends UserLoginPresenter
{
    /**
     * Validate inputs and do Login request
     *
     * @throws
     */
    public function doValidateCredentials($username, $password)
    {
        $model = ModelPool::getModel('lzauser');
        $result = $model->login($username, $this->encryptor->hash($password, 2));

        if (!$result)
        {
            $this->onError('Wrong username or password!');
            return;
        }

        $this->onValidateCredentialsSuccess($result);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onValidateCredentialsSuccess($data = null)
    {
        $data['user_filter'] = [];
        $data['role'] = $data['lzarole_id'];
        unset($data['lzarole_id']);

        $this->session->user = [];
        foreach ($data as $key => $value)
        {
            $this->session->set("user.{$key}", $value);
        }

        $this->session->tokenName = md5('session_security');
        $this->session->tokenValue = $this->csrf->generate($this->session->tokenName);

        if (strcmp($data['expiry'], date('Y-m-d H:i:s')) <= 0)
        {
            $this->viewer->navigateTo(WEBSITE_ROOT . 'lzaadmin/change-password');
        }

        if (isset($this->request->returnUrl))
        {
            $this->viewer->navigateTo(urldecode($this->request->returnUrl));
        }
        elseif ($this->session->get('user.is_admin') === 'Yes')
        {
            $this->viewer->navigateToAdminPanel();
        }
        else
        {
            $this->viewer->navigateToHome();
        }
    }
}
