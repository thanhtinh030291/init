<?php

namespace Lza\App\Client\Utilities;


use Lza\Config\Models\ModelPool;

/**
 * Helper for Fubon Direct Billing Validation
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class FubonHelper implements Helper
{
    /**
     * Check if member has the desired plan/policy year
     *
     * @throws
     */
    public function checkPlan($mbrNo, $incurDate)
    {
        $model = ModelPool::getModel('HbsMember');
        $members = $model->where([
			'company' => 'fubon',
            "trim(leading '0' from mbr_no) =" => $mbrNo,
            'memb_eff_date and ifnull(term_date, memb_exp_date) cover' => $incurDate
        ]);
        $members->select("
            memb_eff_date,
            memb_exp_date,
            term_date,
            memb_rstr
        ");
        if (count($members) === 0)
        {
            $this->session->add('alert_error', $this->i18n->invalidPolicyYear);
            return false;
        }
        return $members;
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

        $model = ModelPool::getModel('FubonBenefit');
        $benefits = $model->select("
            fubon_benefit.id,
            ben_desc{$this->session->lzalanguage} ben_desc,
            ben_type,
            fubon_head.ben_heads
        ");

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

        $sql = $this->sql->validateFubonClaim([
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
        $validations = $this->sql->query($sql, $params, 'hbs_fubon');

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

        $this->controller->deleteTempFubonGops($email);
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
        $this->controller->writeFubonLog(
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
        $sql = $this->sql->getPendingFubonGop([
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
        $model = ModelPool::getModel('FubonBenefit');
        $benefits = $model->select("
            fubon_benefit.id,
            ben_desc{$this->session->lzalanguage} ben_desc,
            ben_type,
            fubon_head.ben_heads
        ");

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

        $sql = $this->sql->fubonMemberBenefit([
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

        return $this->sql->query($sql, $params, 'hbs_fubon');
    }
}
