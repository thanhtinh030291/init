<?php

namespace Lza\App\Client\Modules\Api\V20\Member;


use Exception;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseController;

/**
 * Controller for Member API
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberController extends BaseController
{
    /**
     * Add Mobile User to database
     *
     * @throws
     */
    public function createMobileUser($callback, $member, $bankAccounts, $photo, $langCode, $password , $email_to = null)
    {
        try
        {
            $this->sql->start();
            $model = ModelPool::getModel('MobileUser');
            $result = $model->insert([
                'pocy_no' => $member['pocy_no'],
                'mbr_no' => $member['mbr_no'],
                'password' => $this->encryptor->hash($password, 2),
                'fullname' => trim("{$member['mbr_last_name']} {$member['mbr_first_name']}"),
                'address' => $member['address'],
                'photo' => $this->encryptor->jsonEncode($photo),
                'tel' => $member['tel'],
                'email' => $email_to == null ? $member['email'] : $email_to,
                'language' => $langCode,
                'enabled' => 1
            ]);
            if ($result === false)
            {
                $this->sql->rollback();
                return false;
            }

            $model = ModelPool::getModel('MobileUserBankAccount');
            foreach ($bankAccounts as $account)
            {
                $result = $model->insert([
                    'mobile_user_id' => $result['id'],
                    'bank_name' => $account['bank_name'],
                    'bank_address' => $account['bank_address'],
                    'bank_acc_no' => $account['bank_acc_no'],
                    'bank_acc_name' => $account['bank_acc_name']
                ]);
                if ($result === false)
                {
                    $this->sql->rollback();
                    return false;
                }
            }

            $result = $callback->onAccountCreatedSuccess([
                'username' => 'system',
                'mbr_no' => $member['mbr_no'],
                'fullname' => trim("{$member['mbr_last_name']} {$member['mbr_first_name']}"),
                'email' => $email_to == null ? $member['email'] : $email_to,
                'password' => $password
            ]);
            if ($result === false)
            {
                $this->sql->rollback();
                return false;
            }

            $this->sql->commit();
            return true;
        }
        catch(Exception $e)
        {

        }
        $this->sql->rollback();
        return false;
    }

    /**
     * Add Mobile User Reset Password Token to database
     *
     * @throws
     */
    public function createResetPasswordRequest($callback, $member)
    {
        $model = ModelPool::getModel('MobileUser');
        try
        {
            $this->sql->start();
            
            $password = crypt(time(),mt_rand());
            $result = $model->where('mbr_no', $member['mbr_no'])->update([
                'password' => $this->encryptor->hash($password, 2)
            ]);
            if ($result !== false)
            {
                $result = $callback->onResetPasswordRequestCreatedSuccess([
                    'username' => 'system',
                    'mbr_no' => $member['mbr_no'],
                    'fullname' => $member['fullname'],
                    'email' => $member['email'],
                    'password' => $password
                ]);
                $this->sql->commit();
                return true;
            }
        }
        catch(Exception $e)
        {

        }
        $this->sql->rollback();
        return false;
    }

    /**
     * Update Password to database as Reset requested
     *
     * @throws
     */
    public function resetPassword($callback, $memberNo, $password, $token)
    {
        try
        {
            $this->sql->start();
            $model = ModelPool::getModel('MobileUser');
            $result = $model->where('mbr_no', $memberNo)->update([
                'password' => $this->encryptor->hash($password, 2)
            ]);
            if ($result !== false)
            {
                $result = $this->deleteToken($callback, $token);
                if ($result !== false)
                {
                    $this->sql->commit();
                    return true;
                }
            }
        }
        catch(Exception $e)
        {

        }
        $this->sql->rollback();
        return false;
    }

    /**
     * Delete Mobile User Reset Password Token in database
     *
     * @throws
     */
    public function deleteToken($callback, $token)
    {
        $model = ModelPool::getModel('MobileUserResetPassword', 'main');
        return $model->where('token', $token)->delete();
    }

    /**
     * Update Mobile User Password to database
     *
     * @throws
     */
    public function updatePassword($callback, $member, $password)
    {
        return $member->update([
            'password' => $password
        ]);
    }

    /**
     * Add Mobile User Session to database
     *
     * @throws
     */
    public function createSession($callback, $account)
    {
        $model = ModelPool::getModel('MobileUserSession', 'main');
        try
        {
            $this->sql->start();
            $expire = date('Y-m-d H:i:s', strtotime('+1 month'));
            $token = sha1(time() . mt_rand());
            $session = $model->insert([
                'mbr_no' => $account['mbr_no'],
                'token' => $token,
                'expire' => $expire
            ]);
            if ($session !== false)
            {
                $this->sql->commit();
                return [
                    'fullname' => $account['fullname'],
                    'token' => $session['token'],
                    'mbr_no' => $account['mbr_no'],
                    'email' => $account['email']
                ];
            }
        }
        catch (Exception $e)
        {

        }
        $this->sql->rollback();
        return false;
    }

    /**
     * Add Mobile User Device to database
     *
     * @throws
     */
    public function addDevice($callback, $token, $memberId)
    {
        $model = ModelPool::getModel('MobileDevice');
        try
        {
            $this->sql->start();
            $device = $model->insert([
                'mobile_user_id' => $memberId,
                'device_token' => $token
            ]);
            if ($device !== false)
            {
                $this->sql->commit();
                return $device['id'];
            }
        }
        catch(Exception $e)
        {

        }
        $this->sql->rollback();
        return false;
    }

    /**
     * Update Mobile User Device to database
     *
     * @throws
     */
    public function updateDevice($callback, $device, $memberId)
    {
        return $device->update([
            'mobile_user_id' => $memberId
        ]);
    }

    /**
     * Delete Mobile User Device in database
     *
     * @throws
     */
    public function deleteDevice($callback, $device)
    {
        return $device->delete();
    }

    /**
     * Add Mobile User Bank Account to database
     *
     * @throws
     */
    public function addBankAccount($callback, $account, $memberId)
    {
        $model = ModelPool::getModel('MobileUserBankAccount');
        try
        {
            $this->sql->start();
            $account = $model->insert([
                'mobile_user_id' => $memberId,
                'bank_name' => $account['bank_name'],
                'bank_address' => $account['bank_address'],
                'bank_acc_no' => $account['bank_acc_no'],
                'bank_acc_name' => $account['bank_acc_name']
            ]);
            if ($account !== false)
            {
                $this->sql->commit();
                return $account['id'];
            }
        }
        catch(Exception $e)
        {

        }
        $this->sql->rollback();
        return false;
    }

    /**
     * Update Mobile User Bank Account to database
     *
     * @throws
     */
    public function updateBankAccount($callback, $currentAccount, $account)
    {
        return $currentAccount->update([
            'bank_name' => $account['bank_name'],
            'bank_address' => $account['bank_address'],
            'bank_acc_no' => $account['bank_acc_no'],
            'bank_acc_name' => $account['bank_acc_name']
        ]);
    }

    /**
     * Delete Mobile User Bank Account in database
     *
     * @throws
     */
    public function deleteBankAccount($callback, $account)
    {
        return $account->delete();
    }

    /**
     * Update Mobile User Photo to database
     *
     * @throws
     */
    public function updatePhoto($callback, $member, $photo)
    {
        return $member->update([
            'photo' => $this->encryptor->jsonEncode($photo)
        ]);
    }

    /**
     * Update Mobile User Language to database
     *
     * @throws
     */
    public function updateLanguage($callback, $member, $langCode)
    {
        return $member->update([
            'language' => $langCode
        ]);
    }
}
