<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


// use Lza\App\Client\Modules\Api\V10\ClientApiV10Presenter;
use Lza\Config\Models\ModelPool;

/**
 * Handle Login action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPostLoginPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Login request
     *
     * @throws
     */
    public function doLogin($username, $password)
    {
        $model = ModelPool::getModel('MobileUser');
        
        $accounts = $model->where([
            'email' => $username, 
            'password' => $this->encryptor->hash($password, 2),
            'enabled' => 1
        ]);
        $accounts = $accounts->select('id, mbr_no, fullname, email, company');
        $account = $accounts->fetch();
        
        if ($account === false)
        {
            return false;
        }
        
        $member = $this->isMemberValid($account['mbr_no'], $account['company']);
        if ($member === false)
        {
            // No active policy which effective date around 12 months.
            return false;
        }
            
        return $this->createSession($this, $account);
    }
}
