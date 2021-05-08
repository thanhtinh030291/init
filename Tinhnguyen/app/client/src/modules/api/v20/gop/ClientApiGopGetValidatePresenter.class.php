<?php

namespace Lza\App\Client\Modules\Api\V20\Gop;


use Lza\Config\Models\ModelPool;

/**
 * Handle Validate GOP action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiGopGetValidatePresenter extends ClientApiGopPresenter
{
    /**
     * Validate inputs and do Validate GOP request
     *
     * @var Lza\App\Client\Utilities\PcvHelper
     *
     * @throws
     */
    public function doValidate($helper, $insurer, $mbrNo, $incurDate, $claimLines)
    {
        $model = ModelPool::getModel("{$insurer}_member");
        $mbrNo = intval($mbrNo);
        $members = $model->where([
            'trim(leading 0 from mbr_no) =' => intval($mbrNo),
            'memb_eff_date and memb_exp_date cover' => $incurDate
        ]);
        $member = $members->fetch();
        if ($member === false)
        {
            return false;
        }

        $gops = $helper->getPendingRequests(
            '', $mbrNo, $member['memb_eff_date'], $member['memb_exp_date']
        );
        $gopSqls = [];
        $sql = $this->sql->gopRow;
        foreach ($gops as $gop)
        {
            $gopSqls[] = sprintf($sql,
                $gop['db_ref_no'],
                $gop['ben_type'],
                $gop['ben_head'],
                $gop['ben_desc'],
                $gop['diagnosis'],
                $gop['app_amt']
            );
        }

        foreach ($claimLines as $no => $claimLine)
        {
            $gopSqls[] = sprintf($sql,
                $claimLine['clli_oid'],
                $claimLine['ben_type'],
                $claimLine['ben_head'],
                $claimLine['ben_head'],
                $claimLine['diagnosis'],
                $claimLine['app_amt']
            );
        }

        $sql = $this->sql->validatePcvClaim([
            'gop_sql' => implode('UNION ALL', $gopSqls)
        ]);
        $params = [
            $mbrNo,
            $incurDate,
            $incurDate,
            $incurDate,
            $incurDate,
            $incurDate,
            $incurDate
        ];
        return $this->sql->query($sql, $params, "hbs_{$insurer}");
    }
}
