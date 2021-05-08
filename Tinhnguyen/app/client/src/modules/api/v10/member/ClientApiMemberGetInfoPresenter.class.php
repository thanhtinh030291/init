<?php

namespace Lza\App\Client\Modules\Api\V10\Member;


use DateTime;
use Lza\Config\Models\ModelPool;

/**
 * Handle Get Member Info action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberGetInfoPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Get Member Info request
     *
     * @throws
     */
    public function doGetInfo($memberNo, $company)
    {
        $member = $this->doesMemberExist($memberNo, $company);
        if ($member === false)
        {
            return false;
        }
        unset($member['password']);

        $member['extra'] = $this->getInfo($memberNo, $company);
        return $member;
    }

    /**
     * @throws
     */
    protected function getInfo($memberNo, $company)
    {
        $rows = $this->sql->query(
            "
                SELECT memb.*, config.ready
                FROM hbs_member memb
                LEFT JOIN plan_hbs_config config USING (plan_id, rev_no, company)
                WHERE company = ?
                AND mbr_no = ?
            ", 
            [
                $company, $memberNo
            ]
        );
        
        if ($rows === false || count($rows) === 0)
        {
            return null;
        }
        
        $pocyYears = [];
        foreach($rows as $row)
        {
            $item = [];
            foreach ($row as $key => $value)
            {
                $item[$key] = $value;
            }

            $key = strtotime($item['memb_eff_date']) + strtotime($item['memb_exp_date']);
            $pocyYears[$key] = isset($pocyYears[$key]) ? $pocyYears[$key] : $item;

            $pocyYears[$key]['plans'] = isset($pocyYears[$key]['plans'])
                    ? $pocyYears[$key]['plans'] : explode(';;;', $item['plan_desc']);

            $pocyYears[$key]['events'] = isset($pocyYears[$key]['events'])
                    ? $pocyYears[$key]['events'] : [];
            $item['memb_rstr'] = trim($item['memb_rstr']);
            if (!in_array($item['memb_rstr'], $pocyYears[$key]['events']))
            {
                $pocyYears[$key]['events'][] = $item['memb_rstr'];
            }

            $pocyYears[$key]['events_vi'] = isset($pocyYears[$key]['events_vi'])
                    ? $pocyYears[$key]['events_vi'] : [];
            $item['memb_rstr_vi'] = trim($item['memb_rstr_vi']);
            if (!in_array($item['memb_rstr_vi'], $pocyYears[$key]['events_vi']))
            {
                $pocyYears[$key]['events_vi'][] = $item['memb_rstr_vi'];
            }
        }
        krsort($pocyYears);
        
        $years = [];
        foreach ($pocyYears as $year => $pocyYear)
        {
            if (isset($years['last']))
            {
                $years['previous'] = $pocyYear;
            }
            else
            {
                $years['last'] = $pocyYear;
            }
        }

        foreach ($years as $key => &$pocyYear)
        {
            $pocyYear['insured_periods'] = explode(', ', $pocyYear['insured_periods']);
            foreach ($pocyYear['insured_periods'] as $no => $period)
            {
                if (strlen($period) == 0)
                {
                    unset($pocyYear['insured_periods'][$no]);
                }
            }
            $pocyYear['memb_rstr'] = null;
            $pocyYear['memb_rstr_vi'] = null;

            $date = new DateTime('now');
            if ($pocyYear['payment_mode'] == 'Semi-Annual')
            {
                // $member_eff = new DateTime($pocyYear['memb_eff_date']);
                $member_eff = new DateTime($pocyYear['memb_exp_date']);
                $member_eff->modify('-12 months')->modify('+1 day');
                $member_eff->modify('+6 months')->modify('-1 day');
                $pocyYear['payment_exp'] = $member_eff->format('Y-m-d');

                if ($pocyYear['payment_exp'] < $date->format('Y-m-d'))
                {
                    if ($pocyYear['policy_status'] == "Second payment Policy & Health Card Released")
                    {
                        $pocyYear['policy_status'] = "First payment Policy was expired";
                        $pocyYear['request_next_payment'] = true;
                    }
                    elseif ($pocyYear['policy_status'] == "Approved")
                    {
                        $pocyYear['payment_exp'] = $pocyYear["memb_exp_date"];
                        $pocyYear['request_next_payment'] = false;
                    }
                }
                elseif ($pocyYear['policy_status'] == "Second payment Policy & Health Card Released")
                {
                    $pocyYear['policy_status'] == "First payment Policy & Health Card Released"; // 1 month previous before first payment expired
                }
            }
            else
            {
                // $member_eff = new DateTime($pocyYear['memb_eff_date']);
                $member_eff = new DateTime($pocyYear['memb_exp_date']);
                $member_eff->modify('-12 months')->modify('+1 day'); // pocy_eff_date
                $member_eff->modify('+12 months')->modify('-1 day');
                $pocyYear['payment_exp'] = $member_eff->format('Y-m-d');
                $pocyYear['request_next_payment'] = false;
            }
            $exp_date = new DateTime($pocyYear['payment_exp']);
            $exp_date->modify('+13 months')->format('Y-m-d');
            if ($exp_date > $date->format('Y-m-d'))
            {
                $pocyYear['can_claim'] = true;
            }
            else
            {
                $pocyYear['can_claim'] = false;
            }
        }

        return $years;
    }
}
