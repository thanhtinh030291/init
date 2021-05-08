<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


use Lza\Config\Models\ModelPool;

/**
 * Handle Get Member Devices action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberGetDevicesPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Get Member Devices request
     *
     * @throws
     */
    public function doGetDevices($memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member === false)
        {
            return 0;
        }

        $model = ModelPool::getModel('MobileDevice');
        return $model->where('mobile_user_id', $member['id']);
    }
}
