<?php

namespace Lza\App\Client\Modules\Api\V20\Member;


/**
 * Handle Delete Member Devices action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberDeleteDevicePresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Delete Member Device request
     *
     * @throws
     */
    public function doDeleteDevice($token, $memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member === false)
        {
            return 0;
        }

        $device = $this->doesDeviceExist($token);
        if ($device === false)
        {
            return 1;
        }

        return $this->deleteDevice($this, $device) !== false;
    }
}
