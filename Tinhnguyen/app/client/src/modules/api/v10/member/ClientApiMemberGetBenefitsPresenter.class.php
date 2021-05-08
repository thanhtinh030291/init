<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


use Lza\App\Client\Modules\Api\V10\ClientApiV10Presenter;
use Lza\Config\Models\ModelPool;

/**
 * Handle Get Member Benefits action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberGetBenefitsPresenter extends ClientApiV10Presenter
{
    /**
     * Validate inputs and do Get Member Benefits request
     *
     * @throws
     */
    public function doGetBenefits($memberPlanId, $company, $lang = 'en')
    {
        $model = ModelPool::getModel('HbsMember');
        $members = $model->where(['mepl_oid' => $memberPlanId, 'company' => $company])->select('benefit_' . $lang);
        $member = $members->fetch();
        return $member !== false ? json_decode($member['benefit_' . $lang], true) : 0;
    }
}
