<?php

namespace Lza\App\Client\Modules\Api\V20\Member;


/**
 * Handle Update Member Password action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPatchPasswordPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Update Member Password request
     *
     * @throws
     */
    public function doUpdatePassword($memberNo, $oldPass, $newPass)
    {
        $member = $this->doesMemberExist($memberNo, $oldPass);
        if ($member === false)
        {
            return 0;
        }
        $password = $this->encryptor->hash($newPass, 2);
        return $this->updatePassword($this, $member, $password) !== false;
    }
}
