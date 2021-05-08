<?php

namespace Lza\App\Client\Modules\Api\V10\Member;
use Lza\Config\Models\ModelPool;

/**
 * Handle Register Account action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPostRegisterPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Create Mobile User request
     *
     * @throws
     */
    public function doCreateMobileUser($email, $password, $company, $memberNo, $langCode, $bankAccounts = [], $photo = null)
    {
        $member = $this->doesMemberExist($memberNo, $company);
        if ($member !== false)
        {
            return 0; // da tao acc
        }

        $member = $this->isMemberValid($memberNo, $company);
        if ($member === false)
        {
            return 1;
        }
        
        return $this->createMobileUser(
            $this, $member, $password, $langCode, $bankAccounts, $photo
        );
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onAccountCreatedSuccess($data)
    {
        if (is_null($data['email']) && MOBILE_ALLOW_WITHOUT_EMAIL)
        {
            return true;
        }
        
        $companyName = $this->setting->companyName;
        $email = $this->setting->email;
        $subject = $this->i18n->createMobileUserSubject($companyName);
        $message = $this->i18n->createMobileUserMessage(
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
            $data['email'],
            $subject,
            $message
        );
    }

    public function doCheckPocy($pocy) {
        $model = ModelPool::getModel('HbsMember');
        $members = $model->where('pocy_no = ?', $pocy)->fetch();
        
        if ($members == false) {
            return false;
        }
        return true;
    }

    public function getMembers($pocy_no, $email) {
        return $this->sql->query("
            SELECT DISTINCT mbr_no, company
            FROM hbs_member
            WHERE email = ?
            AND pocy_no = ?
        ", [$email, $pocy_no]);
    }
}
