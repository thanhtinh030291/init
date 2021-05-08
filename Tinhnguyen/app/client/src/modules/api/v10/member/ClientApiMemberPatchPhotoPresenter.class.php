<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


/**
 * Handle Update Member Photo action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPatchPhotoPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Update Member Photo request
     *
     * @throws
     */
    public function doUpdatePhoto($photo, $memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member === false)
        {
            return 0;
        }
        return $this->updatePhoto($this, $member, $photo) !== false;
    }
}
