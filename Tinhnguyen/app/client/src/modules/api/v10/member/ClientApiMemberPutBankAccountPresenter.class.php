<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


/**
 * Handle Update Bank Account action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPutBankAccountPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Update Member Bank Account request
     *
     * @throws
     */
    public function doUpdateBankAccount($account, $id, $memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member === false)
        {
            return 0;
        }

        $currentAccount = $this->doesBankAccountExist($id);
        if ($currentAccount === false)
        {
            return 1;
        }

        return $this->updateBankAccount($this, $currentAccount, $account) !== false;
    }
}
