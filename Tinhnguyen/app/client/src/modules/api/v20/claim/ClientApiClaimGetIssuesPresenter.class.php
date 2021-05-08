<?php

namespace Lza\App\Client\Modules\Api\V20\Claim;


use Lza\Config\Models\ModelPool;

/**
 * Handle Get Claims action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiClaimGetIssuesPresenter extends ClientApiClaimPresenter
{
    /**
     * Validate inputs and do Get Claims request
     *
     * @throws
     */
    public function doGetClaims($memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if (!$member)
        {
            return 0;
        }

        $model = ModelPool::getModel('MobileClaim');
        $claims = $model->where('mobile_user.mbr_no', $memberNo)->order('mantis_id DESC');
        $result = [];
        foreach ($claims as $claim)
        {
            $claim = $this->getClaimExtra($claim);
            $result[] = $claim;
        }
        return $result;
    }
}
