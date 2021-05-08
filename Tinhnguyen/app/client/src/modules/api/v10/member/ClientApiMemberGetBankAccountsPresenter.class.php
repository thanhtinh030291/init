<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


use Lza\Config\Models\ModelPool;

/**
 * Handle Get Bank Accounts action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberGetBankAccountsPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Get Member Bank Accounts request
     *
     * @throws
     */
    public function doGetBankAccounts($memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member === false)
        {
            return 0;
        }

        $model = ModelPool::getModel('MobileUserBankAccount');
        $accounts = $model->where('mobile_user_id', $member['id']);

        $result = [];
        foreach ($accounts as $account)
        {
            $result[] = $account;
        }
        return $result;
    }
}
