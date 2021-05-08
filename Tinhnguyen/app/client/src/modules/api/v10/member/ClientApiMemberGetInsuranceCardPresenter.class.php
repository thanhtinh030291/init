<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


use Lza\App\Client\Utilities\PcvInsuredCardBuilder;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Handle Get Insurance Card action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberGetInsuranceCardPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Get Insurance Card request
     *
     * @throws
     */
    public function doGetInsuranceCard($membNo, $lang = 'en', $company = "pcv")
    {
        $model = ModelPool::getModel('HbsMember');
        $members = $model->where([
			'company' => $company,
			'mbr_no' => $membNo,
			'ben_schedule is not null'
		])->order('memb_eff_date desc');
        $member = $members->fetch();

        if ($member === false)
        {
            return 0;
        }

        $builder = DIContainer::resolve(PcvInsuredCardBuilder::class, $member, $lang);
        return $builder->get();
    }
}
