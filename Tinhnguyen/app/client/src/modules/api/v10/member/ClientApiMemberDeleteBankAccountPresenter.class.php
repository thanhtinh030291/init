<?php

namespace Lza\App\Client\Modules\Api\V10\Member;
use Lza\Config\Models\ModelPool;


/**
 * Handle Delete Member Bank Account action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberDeleteBankAccountPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Delete Member Bank Account request
     *
     * @throws
     */
    public function doDeleteBankAccount($id, $memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member === false)
        {
            return 0;
        }

        $account = $this->doesBankAccountExist($id);
        if ($account === false)
        {
            return 1;
        }

        return $this->deleteBankAccount($this, $account) !== false;
    }

    /**
     * @throws
     */
    private function checkRelatedClaim($bank_id){
        $model = ModelPool::getModel('MobileClaim');
        $condition = [
            'mobile_user_bank_account_id' => $bank_id
        ];
        $claims = $model->where($condition);
        return $claims->fetch();
    }
}
