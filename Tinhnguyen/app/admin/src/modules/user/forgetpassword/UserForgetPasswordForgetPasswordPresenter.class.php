<?php

namespace Lza\App\Admin\Modules\User\ForgetPassword;


use Lza\App\Admin\Modules\AdminPresenter;
use Lza\Config\Models\ModelPool;

/**
 * Handle Create Forget Password Token action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class UserForgetPasswordForgetPasswordPresenter extends AdminPresenter
{
    /**
     * Validate inputs and do Create Reset Password Token request
     *
     * @throws
     */
    public function doForgetPassword($email)
    {
        $model = ModelPool::getModel('lzauser');
        $user = $model->getUserByEmail($email);
        if (!$user)
        {
            $this->onError('Invalid Email!');
            return;
        }
        $this->createRequestPasswordToken($this, $user);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onRequestPasswordTokenCreatedSuccess($data = null)
    {
        $companyName = $this->setting->companyName;
        $fromName = $companyName . ' Inquiry';
        $fromEmail = $this->setting->email;
        $toName = $data['fullname'];
        $toEmail = $data['email'];
        $username = $data['username'];
        $link = WEBSITE_ROOT . "lzaadmin/reset-password/{$data['token']}";
        $subject = $this->i18n->resetpassRequestMessage($companyName);
        $message = $this->i18n->forgetpassEmailContent($toName, WEBSITE_ROOT, $link, $companyName);

        $sent = $this->mailer->add($username, $fromName, $fromEmail, $toName, $toEmail, $subject, $message);
        if ($sent)
        {
            $this->data->errorAlert = $this->i18n->resetpassTokenMessage;
        }
        else
        {
            $this->data->errorAlert = $this->i18n->emailCantSendMessage;
        }
    }
}
