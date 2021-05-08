<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


use Lza\Config\Models\ModelPool;

/**
 * Handle Get Related Members action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberGetRelatedMembersPresenter extends ClientApiMemberGetInfoPresenter
{
    /**
     * Validate inputs and do Get Member Dependants request
     *
     * @throws
     */
    public function doGetRelatedMembers($memberNo, $company = 'pcv')
    {
        $model = ModelPool::getModel('HbsMember');
        $memberNo = intval(str_replace('-', '', $memberNo));
        $members = $model->where(["trim(leading '0' from mbr_no)" => $memberNo, 'company' => $company]);
        $member = $members->fetch();
        if(empty($member['children'])) return [];
        $children = explode(';', $member['children']);

        $members = [];
        $majorityAge = $this->setting->majorityAge;
        foreach ($children as $child)
        {
            list($mbrNo, $mbrName, $mbrAge) = explode(' - ', $child);
            if (intval($mbrAge) < $majorityAge && intval($mbrNo) != intval($memberNo))
            {
                $members[] = [
                    'mbr_no' => $mbrNo,
                    'mbr_name' => ucwords($mbrName),
                    'info' => $this->getInfo($mbrNo)
                ];
            }
        }
        return $members;
    }
}
