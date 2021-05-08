<?php

namespace Lza\App\Client\Modules\Api\V10\Claim;


use Lza\Config\Models\ModelPool;

/**
 * Handle Get Claim action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiClaimGetIssuePresenter extends ClientApiClaimPresenter
{
    /**
     * Validate inputs and do Get Claim request
     *
     * @throws
     */
    public function doGetClaim($id)
    {
        $model = ModelPool::getModel('MobileClaim');
        $claim = $model->where('id', $id)->fetch();
        if ($claim === false)
        {
            return false;
        }

        return $this->getClaimExtra($claim, true);
    }
}
