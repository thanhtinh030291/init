<?php

namespace Lza\App\Client\Modules\Api\V20\Member;


// use Lza\App\Client\Modules\Api\V20\ClientApiV20Presenter;
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
        $accounts = $accounts->select('id, mbr_no, fullname, email');
        $account = $accounts->fetch();
        
        if ($account !== false)
        {
            $member = $this->isMemberValid($account['mbr_no']);
            
            if ($member === false){
                // No active policy which effective date around 12 months.
                return false;
            }
            
            $session = $this->createSession($this, $account);
            return $session;
        }
        return false;
    }
}
