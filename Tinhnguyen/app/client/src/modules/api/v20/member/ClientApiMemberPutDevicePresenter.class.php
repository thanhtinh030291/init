<?php

namespace Lza\App\Client\Modules\Api\V20\Member;


/**
 * Handle Update Device Info action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPutDevicePresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Update Member Device request
     *
     * @throws
     */
    public function doUpdateDevice($token, $memberNo)
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

        return $this->updateDevice($this, $device, $member['id']);
    }
}
