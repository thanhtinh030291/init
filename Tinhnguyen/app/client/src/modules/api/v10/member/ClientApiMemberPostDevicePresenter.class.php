<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


/**
 * Handle Add Member Device action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPostDevicePresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Add Device request
     *
     * @throws
     */
    public function doAddDevice($token, $memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member === false)
        {
            return 0;
        }

        $device = $this->doesDeviceExist($token);
        if ($device !== false)
        {
            return 1;
        }

        return $this->addDevice($this, $token, $member['id']);
    }
}
