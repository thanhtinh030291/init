<?php

namespace Lza\App\Client\Utilities;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * Convert PCV Member data to Card
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PcvIssuedCardAdapter implements IssuedCardAdapter
{
    /**
     * @var string Date Format to be shown
     */
    private $dateFormat;

    public static $limitTypes = [
        "amt_vis",
        "amt_day",
        "amt_yr",
        "amt_life",
        "amt_dis_vis",
        "amt_dis_yr",
        "amt_dis_life",
        "vis_day",
        "vis_day_yr",
        "day_dis_yr",
        //"ded_amt"
    ];

    /**
     * @throws
     */
    public function __construct()
    {
        $this->dateFormat = DATE_FORMAT;

        $planDescs = explode(';;;', trim($this->member['plan_desc'], ';;;'));
        $planDescCount = count($planDescs);
        if ($planDescCount > 1)
        {
            for ($i = 0, $c = count($planDescs); $i < $c; ++$i)
            {
                $planDescs[$i] = ($i + 1) . ': ' . $planDescs[$i];
            }
        }
        $this->member['plan_desc'] = $planDescs;

        $insuredPeriods = [];
        $insuredPeriods2 = $this->getInsuredPeriods($this->member['insured_periods']);

        foreach ($insuredPeriods2 as $insuredPeriod)
        {
            $effDate = date_create_from_format('Y-m-d', $insuredPeriod['eff_date']);
            $effDate = !$effDate ? null : $effDate->format($this->dateFormat);
            $expDate = date_create_from_format('Y-m-d', $insuredPeriod['exp_date']);
            $expDate = !$expDate ? null : $expDate->format($this->dateFormat);

            $insuredPeriods[] = "{$effDate} - {$expDate}";
        }
        $this->member['insured_periods'] = $insuredPeriods;

        $this->member['memb_status_notes'] = [];
        if (strlen($this->member['memb_status_note']) > 0)
        {
            $this->member['memb_status_notes'][] = $this->i18n->{$this->member['memb_status_note']};
        }
        if (strlen($this->member['memb_eff_date_note']) > 0)
        {
            $this->member['memb_status_notes'][] = $this->i18n->{$this->member['memb_eff_date_note']};
        }
        if (strlen($this->member['memb_exp_date_note']) > 0)
        {
            $this->member['memb_status_notes'][] = $this->i18n->{$this->member['memb_exp_date_note']};
        }
        if (strlen($this->member['term_date_note']) > 0)
        {
            $this->member['memb_status_notes'][] = $this->i18n->{$this->member['term_date_note']};
        }
        if (strlen($this->member['reinst_date_note']) > 0)
        {
            $this->member['memb_status_notes'][] = $this->i18n->{$this->member['reinst_date_note']};
        }

        $this->member['can_gop'] = true;
        if (
            in_array($this->i18n->membNotPaidYet, $this->member['memb_status_notes']) ||
            in_array($this->i18n->membHasNoBenefit, $this->member['memb_status_notes']) ||
            in_array($this->i18n->cardGoExpired, $this->member['memb_status_notes']) ||
            in_array($this->i18n->cardIsExpired, $this->member['memb_status_notes'])
        )
        {
            $this->member['can_gop'] = false;
        }

        $diagnosis = mb_strtolower($this->session->search['diagnosis'] ?? '');

        $includedDiags = explode(', ', $this->member['included_diags']);
        $included = false;
        foreach ($includedDiags as $diag)
        {
            if (empty($diag) || empty($diagnosis))
            {
                continue;
            }

            $diag = mb_strtolower($diag);
            $score = similar_text($diag, $diagnosis);
            if (
                $score / strlen($diag) > DIAG_SCORE ||
                $score / strlen($diagnosis) > DIAG_SCORE ||
                strpos($diag, $diagnosis) !== false
            )
            {
                $included = true;
                break;
            }
        }

        if (!$included)
        {
            $this->member['excluded_diags'] = trim($this->member['excluded_diags'], ', ');
            $excludedDiags = explode(', ', $this->member['excluded_diags']);
            $model = ModelPool::getModel('PcvDiagExcl');
            $diags = $model->where('1=1');
            foreach ($diags as $diag)
            {
                $excludedDiags[] = $diag['diag_desc'];
            }

            $excludedDiags = array_unique($excludedDiags);
            foreach ($excludedDiags as $diag)
            {
                if (empty($diag) || empty($diagnosis))
                {
                    continue;
                }
                $diag = mb_strtolower($diag);
                $score = similar_text($diag, $diagnosis);
                if (
                    $score / strlen($diag) > DIAG_SCORE ||
                    $score / strlen($diagnosis) > DIAG_SCORE ||
                    strpos($diag, $diagnosis) !== false
                )
                {
                    $this->session->add(
                        'alert_error',
                        sprintf(
                            '<strong>%s</strong><br />%s',
                            $this->i18n->diagnosis,
                            $this->i18n->membEventNote(
                                $this->session->search['diagnosis'],
                                $this->member['excluded_diags'],
                                WEBSITE_ROOT . 'excl-diags/pcv'
                            )
                        )
                    );
                }
            }
        }

        $this->member['memb_status_notes'] = count($this->member['memb_status_notes'])
                ? '<b class="text-danger">' . $this->i18n->warning . '</b>:<br />• ' . implode('<br />• ', array_unique($this->member['memb_status_notes']))
                : '';

        if ($this->member['spec_dis_period'] === 'Yes')
        {
            $this->member['memb_event'] = "• " . $this->i18n->specialDiseaseWaitingEnd
                . ": {$this->member['special_disease_waiting_end']}\n"
                . $this->member['memb_event'];
        }

        $helper = DIContainer::resolve(PcvHelper::class);
        $limits = $helper->getBenefits(
            $this->member['mbr_no'],
            $this->member['memb_eff_date'],
            $this->member['memb_exp_date']
        );

        $this->member['limits'] = [];
        $model = ModelPool::getModel('HbsMember');
        $plans = $model->where([
			'company' => 'pcv',
            "trim(leading '0' from mbr_no) =" => intval($this->member['mbr_no']),
            'memb_eff_date' => $this->member['memb_eff_date']
        ]);
        $plans = $plans->select("
            plan_desc,
            MAX(IF(op_ind = 'No', NULL, op_ind)) op_ind,
            MAX(IF(dt_ind = 'No', NULL, dt_ind)) dt_ind
        ");
        $plans = $plans->group("plan_desc");
        foreach ($plans as $plan)
        {
            foreach ($limits as $limit)
            {
                if ($plan['plan_desc'] === $limit['plan'])
                {
                    if (
                        ($plan['op_ind'] === 'No' && $limit['ben_type'] === 'OP') ||
                        ($plan['dt_ind'] === 'No' && $limit['ben_type'] === 'DT')
                    )
                    {
                        continue;
                    }
                    $this->member['limits'][$limit['plan']] = $this->member['limits'][$limit['plan']] ?? [];
                    $this->member['limits'][$limit['plan']][$limit['ben_desc']] = $limit;
                }
            }
        }
    }

    /**
     * Get data to be displayed on the web page
     *
     * @throws
     */
    public function getDisplayData()
    {
        return $this->member;
    }

    /**
     * Get data to be displayed on the email
     *
     * @throws
     */
    public function getHtmlData()
    {
        $memberStatus = '';
        if (strlen($this->member['memb_status']))
        {
            $memberStatus .= sprintf(
                self::EMAIL_ROW,
                $this->i18n->membStatus,
                $this->i18n->{$this->member['memb_status']},
                $this->member['memb_status_notes']
            );
        }

        $termDate = '';
        if (strlen($this->member['term_date']))
        {
            $termDate .= sprintf(
                self::EMAIL_ROW,
                $this->i18n->termDate,
                $this->member['term_date'],
                ''
            );
        }

        $insuredPeriods = '';
        if (count($this->member['insured_periods']) > 1)
        {
            $insuredPeriods = sprintf(
                self::EMAIL_ROW,
                $this->i18n->insuredPeriods,
                implode('<br />', $this->member['insured_periods']),
                ''
            );
        }
        else
        {
            $insuredPeriods = sprintf(
                self::EMAIL_ROW,
                $this->i18n->firstEffectiveDate,
                $this->member['min_memb_eff_date'],
                ''
            );
        }

        $waitPeriod = sprintf(
            self::EMAIL_ROW,
            $this->i18n->waitPeriod,
            $this->i18n->{$this->member['wait_period']},
            ''
        );

        $reinstDate = '';
        if (strlen($this->member['reinst_date']))
        {
            $reinstDate .= sprintf(
                self::EMAIL_ROW,
                $this->i18n->reinstDate,
                $this->member['reinst_date'],
                ''
            );
        }

        $planLimit = '';
        foreach ($this->member['limits'] as $plan => $planLimits)
        {
            $planLimit .= '<tr><td colspan="2">' . $plan . ':</td></tr>';
            foreach ($planLimits as $title => $limits)
            {
                foreach (self::$limitTypes as $type)
                {
                    if ($limits[$type . '_limit'] !== null)
                    {
                        $planLimit .= sprintf(
                            self::EMAIL_ROW,
                            $this->i18n->benLimitFor($this->i18n->$type, $title),
                            number_format($limits[$type . '_limit']) . ' VND',
                            ''
                        );
                        $planLimit .= sprintf(
                            self::EMAIL_ROW,
                            $this->i18n->benUsedFor($this->i18n->$type, $title),
                            number_format($limits[$type . '_limit'] - $limits[$type . '_used']) . ' VND',
                            ''
                        );
                        if (strpos($type, '_dis_') === false)
                        {
                            $planLimit .= sprintf(
                                self::EMAIL_ROW,
                                $this->i18n->benRemainedFor($this->i18n->$type, $title),
                                number_format($limits[$type . '_limit'] - $limits[$type . '_used']) . ' VND',
                                ''
                            );
                        }
                    }
                }
            }
        }

        $memberEvent = '';
        if (strlen($this->member['memb_event']))
        {
            $memberEvent .= sprintf(
                self::EMAIL_ROW,
                $this->i18n->membEvent,
                '<span style="color: red">' . nl2br($this->member['memb_event']) . '</span>',
                ''
            );
        }

        $dbRefNo = '';
        if (isset($this->member['db_ref_no']))
        {
            $memberEvent .= sprintf(
                self::EMAIL_ROW,
                $this->i18n->dbRefNo,
                '<span style="color: red">' . $this->member['db_ref_no'] . '</span>',
                ''
            );
        }

        $this->member['html'] = sprintf(
            self::EMAIL_BODY,
            $this->i18n->membInfo,
            sprintf(
                self::EMAIL_ROW,
                $this->i18n->planDesc,
                implode('<br />', $this->member['plan_desc']),
                ''
            ),
            sprintf(
                self::EMAIL_ROW,
                $this->i18n->mbrName,
                $this->member['mbr_name'],
                ''
            ),
            sprintf(
                self::EMAIL_ROW,
                $this->i18n->mbrNo,
                $this->member['mbr_no'],
                ''
            ),
            sprintf(
                self::EMAIL_ROW,
                $this->i18n->policyNo,
                $this->member['pocy_no'],
                ''
            ),
            sprintf(
                self::EMAIL_ROW,
                $this->i18n->paymentMode,
                $this->i18n->{$this->member['payment_mode']},
                ''
            ),
            $memberStatus,
            sprintf(
                self::EMAIL_ROW,
                $this->i18n->membEffDate,
                $this->member['memb_eff_date'],
                $this->i18n->{$this->member['memb_eff_date_note']}
            ),
            sprintf(
                self::EMAIL_ROW,
                $this->i18n->membExpDate,
                $this->member['memb_exp_date'],
                $this->i18n->{$this->member['memb_exp_date_note']}
            ),
            $termDate,
            $insuredPeriods,
            $waitPeriod,
            $reinstDate,
            $planLimit,
            $memberEvent,
            $dbRefNo
        );
        return $this->member;
    }

    private function getInsuredPeriods($data)
    {
        $result = [];
        $periods = explode(', ', $data);
        foreach ($periods as $period)
        {
            list($effDate, $expDate) = explode(' - ', $period);
            $result[] = [
                'eff_date' => trim($effDate),
                'exp_date' => trim($expDate)
            ];
        }
        return $result;
    }
}
