<?php

namespace Lza\App\Client\Modules\Api\V20\Member;


/**
 * Handle Add Member Bank Account action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPostBankAccountPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Bank Account request
     *
     * @throws
     */
    public function doAddBankAccount($account, $memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member === false)
        {
            return 0;
        }

        $existingAccount = $this->doesBankAccountExistByBankNameAndAccNo(
            $account['bank_name'],
            $account['bank_acc_no']
        );
        if ($existingAccount !== false)
        {
            return 1;
        }

        return $this->addBankAccount($this, $account, $member['id']);
    }
}
