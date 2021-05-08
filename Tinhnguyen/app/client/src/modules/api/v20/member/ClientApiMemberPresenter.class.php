<?php

namespace Lza\App\Client\Modules\Api\V20\Member;


use Lza\App\Client\Modules\Api\V20\ClientApiV20Presenter;
use Lza\Config\Models\ModelPool;

/**
 * Base Presenter for Member API
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPresenter extends ClientApiV20Presenter
{
    /**
     * Validate inputs and check if Member exists
     *
     * @throws
     */
    public function doesMemberExist($memberNo, $oldPass = null)
    {
        $model = ModelPool::getModel('MobileUser');
        $condition = [
            'mbr_no' => $memberNo,
            'enabled' => 1
        ];
        if ($oldPass !== null)
        {
            $condition['password'] = $this->encryptor->hash($oldPass, 2);
        }
        $accounts = $model->where($condition);
        return $accounts->fetch();
    }
    
    /**
     * Validate inputs and check if Member exists
     *
     * @throws
     */
    public function doesMemberExistByEmail($email, $oldPass = null)
    {
        $model = ModelPool::getModel('MobileUser');
        $condition = [
            'email' => $email,
            'enabled' => 1
        ];
        if ($oldPass !== null)
        {
            $condition['password'] = $this->encryptor->hash($oldPass, 2);
        }
        $accounts = $model->where($condition);
        return $accounts->fetch();
    }
    /**
     * Validate inputs and check if Member exists by his/her Dependant
     *
     * @throws
     */
    public function doesMemberExistByDependant($memberNo)
    {
        $model = ModelPool::getModel('MobileClaim');
        $accounts = $model->where('dependent_memb_no', $memberNo)->select('mobile_user.*');
        return $accounts->fetch();
    }

    /**
     * Validate inputs and check if Language exists
     *
     * @throws
     */
    public function doesLanguageExist($code)
    {
        $model = ModelPool::getModel('Lzalanguage');
        $languages = $model->where('code', $code);
        return $languages->fetch();
    }

    /**
     * Validate inputs and check if Device exists
     *
     * @throws
     */
    public function doesDeviceExist($token)
    {
        $model = ModelPool::getModel('MobileDevice');
        $devices = $model->where('device_token', $token);
        return $devices->fetch();
    }

    /**
     * Validate inputs and check if Bank Account exists
     *
     * @throws
     */
    public function doesBankAccountExist($id)
    {
        $model = ModelPool::getModel('MobileUserBankAccount');
        $accounts = $model->where('id', $id);
        return $accounts->fetch();
    }

    /**
     * Validate inputs and check if Bank Account exists by Bank Name
     *
     * @throws
     */
    public function doesBankAccountExistByBankNameAndAccNo($bankName, $accNo)
    {
        $model = ModelPool::getModel('MobileUserBankAccount');
        $accounts = $model->where([
            'bank_name' => $bankName,
            'bank_acc_no' => $accNo
        ]);
        return $accounts->fetch();
    }

    /**
     * @throws
     */
    public function isMemberValid($memberNo, $internal=false, $company ='pcv')
    {
        $model = ModelPool::getModel('HbsMember');
        if($internal){
            $members = $model->where([
                'company' => $company,
                'trim(leading 0 from mbr_no) =' => intval($memberNo),
                'timestampdiff(year, dob, NOW()) >' => $this->setting->adultAge,
                'pocy_no =' => '01002200000003'
            ]);
        } else {
            $members = $model->where([
                'trim(leading 0 from mbr_no) =' => intval($memberNo),
                'timestampdiff(year, dob, NOW()) >' => $this->setting->adultAge
            ]);
        }
        $member = $members->fetch();
        
        if (!$member)
        {
            return false;
        }
        return $member;
    }
}
