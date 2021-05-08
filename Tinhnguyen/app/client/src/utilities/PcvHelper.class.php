<?php

namespace Lza\App\Client\Utilities;


use Lza\Config\Models\ModelPool;

/**
 * Helper for Pcv Direct Billing Validation
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PcvHelper implements Helper
{
    /**
     * Check if member has the desired plan/policy year
     *
     * @throws
     */
    public function checkPlan($mbrNo, $incurDate, $gops)
    {
        $model = ModelPool::getModel('HbsMember');
        $members = $model->where([
			'company' => 'pcv',
            "trim(leading '0' from mbr_no) =" => $mbrNo,
            'memb_eff_date and ifnull(term_date, memb_exp_date) cover' => $incurDate
        ]);
        $members->select("
            IF(min_memb_eff_date = memb_eff_date, 'Y', 'N') first_year,
            memb_eff_date,
            memb_exp_date,
            term_date,
            memb_rstr,
            MAX(IF(op_ind = 'No', NULL, op_ind)) op_ind,
            MAX(IF(dt_ind = 'No', NULL, dt_ind)) dt_ind
        ");
        $members->group("
            min_memb_eff_date,
            memb_eff_date,
            memb_exp_date,
            term_date
        ");
        if (count($members) === 0)
        {
            $this->session->add('alert_error', $this->i18n->invalidPolicyYear);
            return false;
        }
        $member = $members->fetch();

        $model = ModelPool::getModel('PcvBenefit');
        $benefits = $model->where('1=1');
        $check = true;
        foreach ($benefits as $benefit)
        {
            foreach ($gops as $gop)
            {
                if ($gop['ben_head'] === $benefit['pcv_head_id'])
                {
                    if (
                        ($benefit['ben_type'] === 'OP' && $member['op_ind'] === null) ||
                        ($benefit['ben_type'] === 'DT' && $member['dt_ind'] === null)
                    )
                    {
                        $this->session->add('alert_error', $this->i18n->invalidMemberBenefit(
                            $benefit['ben_desc' . $this->session->lzalanguage]
                        ));
                        $check = false;
                    }
                }
            }
        }

        return $check ? $member : false;
    }

    /**
     * Check if member has the benefits or not
     *
     * @throws
     */
    public function checkBenefits($benefits, $provId, $isFirstYear)
    {
        $model = ModelPool::getModel('PcvBenefit');
        foreach ($benefits as $id => $benefit)
        {
            $benefit = $model->where('pcv_head_id', $benefit['ben_head'])->fetch();
            if ($benefit === false)
            {
                $this->session->add('alert_error', $this->i18n->invalidBenefit);
                return false;
            }

            if ($benefit['no_first_year'] === 'Y' && $isFirstYear)
            {
                $this->session->add('alert_error', $this->i18n->invalidMemberBenefit(
                    $benefit['ben_desc' . $this->session->lzalanguage]
                ));
                return false;
            }

            $providers = $benefit->pcv_benefit_provider();
            if (count($providers) > 0)
            {
                $allow = false;
                foreach ($providers as $provider)
                {
                    if ($provider['provider_id'] == $provId)
                    {
                        $allow = true;
                        break;
                    }
                }

                if (!$allow)
                {
                    $this->session->add('alert_error', $this->i18n->invalidProvider);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check if member is over limit or not
     *
     * @throws
     */
    public function checkLimits(
        $email, $ipAddress, $time, $langCode, $mbrNo, $dob, $effDate, $expDate,
        $provId, $diagnosis, $incurDate, $benefits, $note, $callTime, $telNo
    )
    {
        $this->addPendingRequests(
            $email, $ipAddress, $time, $mbrNo, $dob, $provId,
            $diagnosis, $incurDate, $benefits, $note, $callTime, $telNo
        );
        $gops = $this->getPendingRequests($langCode, $mbrNo, $effDate, $expDate);
        if (count($gops) === 0)
        {
            $this->session->add('alert_error', $this->i18n->invalidGopExportData);
            return false;
        }

        $model = ModelPool::getModel('PcvBenefit');
        $benefits = $model->select("
            pcv_benefit.id,
            ben_desc{$this->session->lzalanguage} ben_desc,
            ben_type,
            pcv_head.ben_heads
        ");
        $benefits = $benefits->where("gender in ('B', ?)", $this->session->search['gender']);

        // convert benefits from MySQL to Oracle
        $benefitSqls = [];
        $sql = $this->sql->benefitRow;
        $i = 0;
        foreach ($benefits as $benefit)
        {
            $i++;
            $benefitSqls[] = sprintf(
                $sql,
                $benefit['id'],
                $benefit['ben_type'],
                $benefit['ben_heads'],
                $benefit['ben_desc'],
                $i
            );
        }

        // convert pending requests from MySQL to Oracle
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

        $sql = $this->sql->validatePcvClaim([
            'gop_sql' => implode('UNION ALL', $gopSqls),
            'bene_sql' => implode('UNION ALL', $benefitSqls)
        ]);
        $params = [
            $mbrNo,
            $incurDate->format('Y-m-d'),
            $incurDate->format('Y-m-d'),
            $incurDate->format('Y-m-d'),
            $incurDate->format('Y-m-d'),
            $incurDate->format('Y-m-d'),
            $incurDate->format('Y-m-d')
        ];
        $validations = $this->sql->query($sql, $params, 'hbs_pcv');

        if (count($validations) === 0)
        {
            return true;
        }

        foreach ($validations as $validation)
        {
            $limType = strtolower($validation['limit_type']);
            if ($validation['ben_limit'] == null)
            {
                $this->session->add('alert_error', $this->i18n->invalidMemberBenefit(
                    $validation['ben_desc']
                ));
            }
            elseif ($validation['ben_head'] !== null)
            {
                $this->session->add('alert_error', $this->i18n->invalidAmtGopFor(
                    number_format($validation['ben_spent']),
                    $validation['ben_desc'],
                    $this->i18n->{$limType},
                    number_format($validation['ben_limit'])
                ));
            }
            else
            {
                $this->session->add('alert_error', $this->i18n->invalidAmtGop(
                    number_format($validation['ben_spent']),
                    $this->i18n->{$limType},
                    number_format($validation['ben_limit'])
                ));
            }
        }

        $this->controller->deleteTempPcvGops($email);
        return false;
    }

    /**
     * Add pending requests to the history
     *
     * @throws
     */
    private function addPendingRequests(
        $email, $ipAddress, $time, $mbrNo, $dob, $provId,
        $diagnosis, $incurDate, $benefits, $note, $callTime, $telNo
    )
    {
        // Temporary add current requests
        $this->controller->writePcvLog(
            $email, $ipAddress, $time, $mbrNo, $dob, $provId, $diagnosis, $note,
            $callTime, $telNo, $benefits, $incurDate, 'temp', null, 'Pending'
        );
    }

    /**
     * Retrieve all pending requests
     *
     * @throws
     */
    public function getPendingRequests($langCode, $mbrNo, $effDate, $expDate)
    {
        // Get all pending requests, including current ones
        $sql = $this->sql->getPendingPcvGop([
            'lang_code' => $langCode
        ]);
        return $this->sql->query($sql, [
            intval($mbrNo), $effDate, $expDate
        ]);
    }

    /**
     * Retrieve all benefits
     *
     * @throws
     */
    public function getBenefits($mbrNo, $effDate, $expDate)
    {
        $model = ModelPool::getModel('PcvBenefit');
        $benefits = $model->select("
            pcv_benefit.id,
            ben_desc{$this->session->lzalanguage} ben_desc,
            ben_type,
            pcv_head.ben_heads
        ");
        $benefits = $benefits->where("gender in ('B', ?)", $this->session->search['gender']);

        // convert benefits from MySQL to Oracle
        $benefitSqls = [];
        $sql = $this->sql->benefitRow;
        $i = 0;
        foreach ($benefits as $benefit)
        {
            $i++;
            $benefitSqls[] = sprintf(
                $sql,
                $benefit['id'],
                $benefit['ben_type'],
                $benefit['ben_heads'],
                $benefit['ben_desc'],
                $i
            );
        }

        $gops = $this->getPendingRequests(
            $this->session->lzalanguage,
            $mbrNo,
            $effDate,
            $expDate
        );

        // convert pending requests from MySQL to Oracle
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

        if (!count($gopSqls))
        {
            $gopSqls[] = sprintf($sql, null, null, null, null, null, 0);
        }

        $sql = $this->sql->pcvMemberBenefit([
            'gop_sql' => implode('UNION ALL', $gopSqls),
            'bene_sql' => implode('UNION ALL', $benefitSqls)
        ]);

        $params = [
            intval($mbrNo),
            $effDate,
            $effDate,
            $effDate,
            $effDate,
            $effDate,
            $effDate
        ];

        return $this->sql->query($sql, $params, 'hbs_pcv');
    }
}
