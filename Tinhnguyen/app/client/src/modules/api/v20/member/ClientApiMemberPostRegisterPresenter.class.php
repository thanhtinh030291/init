<?php

namespace Lza\App\Client\Modules\Api\V20\Member;
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
    public function doCreateMobileUser($memberNo, $bankAccounts, $photo, $langCode, $password, $email)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member !== false)
        {
            return 0; // da tao acc
        }

        $member = $this->isMemberValid($memberNo, false);
        if ($member === false)
        {
            return 1; 
        }
        

        return $this->createMobileUser(
            $this, $member, $bankAccounts, $photo, $langCode, $password , $email
        );
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onAccountCreatedSuccess($data)
    {
        if (is_null($data['email']) && MOBILE_ALLOW_WITHOUT_EMAIL){
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

    public function doCheckPocy($pocy){
        $model = ModelPool::getModel('HbsMember');
        $members = $model->where('pocy_no = ?', $pocy)->fetch();
        
        if($members== false){
            return false;
        }else{
            return true;
        }
    }

    public function  checkUnitOrNotEmail($pocy_no, $email){
        return $this->sql->query("
            SELECT mbr_no
            FROM (
                            SELECT mbr_no
                            FROM pcv_member
                            WHERE email = ?
                            AND pocy_no = ?
                            GROUP BY mbr_no, email
            ) mb
        ",[
            $email, $pocy_no
        ]);
    }
}
