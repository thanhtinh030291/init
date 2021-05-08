<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


use Lza\App\Client\Modules\Api\V10\ClientApiV10Presenter;
use Lza\Config\Models\ModelPool;

/**
 * Handle Update Forotten Password action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPatchForgetPasswordPresenter extends ClientApiV10Presenter
{
    /**
     * Validate inputs and do Reset Member Password request
     *
     * @throws
     */
    public function doResetPassword($memberNo, $password, $token)
    {
        $model = ModelPool::getModel('MobileUserResetPassword', 'main');
        $accounts = $model->where([
            'mbr_no' => $memberNo,
            'token' => $token
        ]);
        $accounts = $accounts->select('IF(expire > NOW(), 1, 0) result');
        $account = $accounts->fetch();
        if ($account === false)
        {
            return 0;
        }
        if ($account['result'] === 0)
        {
            $this->deleteToken($this, $token);
            return -1;
        }

        $result = $this->resetPassword($this, $memberNo, $password, $token);
        if ($result !== false)
        {
            return 1;
        }
        return -2;
    }
}
