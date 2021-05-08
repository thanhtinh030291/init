<?php

namespace Lza\App\Admin\Modules\User;


use Lza\App\Admin\Modules\AdminController;
use Lza\Config\Models\ModelPool;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UsersController extends AdminController
{
    /**
     * Insert Request Password Token to database
     *
     * @throws
     */
    public function createRequestPasswordToken($callback, $user)
    {
        $model = ModelPool::getModel('UserResetPassword');
        $model->where('email = ?', $user['email'])->delete();

        $token = md5($user['username'] . date('Y-m-d-H-i-s'));
        $result = $model->insert([
            'email' => $user['email'],
            'token' => $token,
            'expire' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
        if ($result)
        {
            $callback->onRequestPasswordTokenCreatedSuccess([
                'fullname' => $user['fullname'],
                'email' => $user['email'],
                'token' => $token
            ]);
        }
        else
        {
            $callback->onError('Failed to create token string!');
        }
    }

    /**
     * Update new user password to database
     *
     * @throws
     */
    public function changePassword($callback, $user, $password)
    {
        $result = $user->update([
            "password" => $this->encryptor->hash($password, 2),
            'expiry' => date('Y-m-d H:i:s', strtotime('+ ' . PASSWORD_EXPIRED_PERIOD))
        ]);
        if ($result)
        {
            $callback->onPasswordChangedSuccess($user);
        }
        else
        {
            $callback->onError('Invalid Password!');
        }
    }

    /**
     * Update Password to database as Reset requested
     *
     * @throws
     */
    public function resetPassword($callback, $user, $password = null)
    {
        $result = $user->update([
            "password" => $this->encryptor->hash($password, 2),
            'expiry' => date('Y-m-d H:i:s', strtotime('+ ' . PASSWORD_EXPIRED_PERIOD))
        ]);
        if ($result !== false)
        {
            $model = ModelPool::getModel('UserResetPassword');
            $model->where('email', $user['email'])->delete();
            $callback->onPasswordResetedSuccess($user);
        }
        else
        {
            $callback->onError('Invalid Password!');
        }
    }
}
