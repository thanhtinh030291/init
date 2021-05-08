<?php

namespace Lza\App\Client\Modules\Api\V20\Member;
use Lza\Config\Models\ModelPool;


/**
 * Handle Create Reset Password Token action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPostForgetPasswordPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Create Reset Password Token request
     *
     * @throws
     */
    public function doCreateResetPasswordRequest($email)
    {
        $member = $this->doesMemberExistByEmail($email);
        if ($member === false)
        {
            return 0;
        }
        return $this->createResetPasswordRequest($this, $member);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onResetPasswordRequestCreatedSuccess($data, $company ='pcv')
    {
        $model = ModelPool::getModel('HbsMember');
        $member = $model->where([
			'company' => $company,
		    'mbr_no' => $data['mbr_no']
		])->fetch();

        $userEmail = $member['email'] ?? $data['email'];

        $companyName = $this->setting->companyName;
        $email = $this->setting->email;
        $subject = $this->i18n->createResetPasswordRequestSubject($companyName);
        $message = $this->i18n->createResetPasswordRequestMessage(
            $data['fullname'],
            $companyName,
            $data['email'],
            $data['password']
        );

        return $this->mailer->add(
            $data['username'],
            "$companyName Enquiry",
            $email,
            $data['fullname'],
            $userEmail,
            $subject,
            $message
        );
    }
}
