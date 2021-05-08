<?php

namespace Lza\App\Client\Modules\Api\V20\Claim;


/**
 * Handle Get Claim OTP action
 *
 * @var i18n
 * @var smsHandler
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiClaimGetOneTimePasswordPresenter extends ClientApiClaimPresenter
{
    /**
     * Validate inputs and do Get OTP request
     *
     * @throws
     */
    public function doGetOneTimePassword($memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if (!$member)
        {
            return 0;
        }

        if ($member['tel'] === null)
        {
            return 1;
        }

        return $this->addOneTimePasswordRequest($this, $member);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onSuccess($data = null)
    {
        $expire = date(DATETIME_FORMAT, strtotime($data['expire']));
        $message = $this->i18n->otpMessage($data['otp'], $expire);
        $this->smsHandler->sendSms($data['tel'], $message);
    }
}
