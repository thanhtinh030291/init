SELECT
    M.`mbr_no`,
    M.`dob`,
    M.`mbr_name`,
    M.`pocy_no`,
    M.`plan_desc`,
    M.`payment_mode`,
    M.`memb_status`,
    M.`memb_status_note`,
    M.`policy_status`,
    M.`insured_periods`,
    M.`memb_eff_date`,
    M.`memb_eff_date_note`,
    M.`memb_exp_date`,
    M.`memb_exp_date_note`,
    M.`term_date`,
    M.`term_date_note`,
    M.`reinst_date`,
    M.`reinst_date_note`,
    M.`min_memb_eff_date`,
    M.`special_disease_waiting_end`,
    M.`wait_period`,
    M.`spec_dis_period`,
    IF(M.`memb_event` = '•', NULL, M.`memb_event`) `memb_event`,
    M.`included_diags`,
    M.`excluded_diags`,
    M.`is_renew`,
    M.`op_ind`,
    M.`dt_ind`,
    '' `db_ref_no`
FROM (
    SELECT DISTINCT
        M.`mbr_no`,
        M.`dob`,
        CONCAT(M.`mbr_last_name`, ' ', M.`mbr_first_name`) `mbr_name`,
        SUBSTRING_INDEX(
            SUBSTRING_INDEX(
                M.`pocy_ref_no`,
                '.', 1
            ),
            ' ', -1
        ) `pocy_no`,
        GROUP_CONCAT(DISTINCT
            GET_PCV_PLAN_DESC(M.`plan_desc`)
            SEPARATOR ';;;'
        ) `plan_desc`,
        CASE
            WHEN M.`payment_mode` = 'Semi-Annual'
                THEN 'semi_annual'
            WHEN M.`payment_mode` = 'Annual'
                THEN 'annual'
            ELSE M.`payment_mode`
        END `payment_mode`,
        M.`policy_status`,
        CASE
            WHEN (
                    M.`min_memb_eff_date` > '{incur_date}' - INTERVAL 30 DAY AND
                    M.`wait_period` = 'Yes'
                )
                OR (
                    M.`reinst_date` IS NOT NULL AND
                    M.`reinst_date` > '{incur_date}' - INTERVAL 9 DAY
                )
                OR IFNULL(M.`term_date`, M.`memb_exp_date`) < '{incur_date}'
                OR MAX(IF(M.`has_op_debit_note` = 'No', NULL, 'Yes')) != 'Yes'
                OR (
                    M.`payment_mode` = 'Annual' AND
                    M.`policy_status` != 'Approved'
                )
                OR (
                    M.`payment_mode` = 'Semi-Annual' AND
                    (
                        (
                            M.`policy_status` NOT IN ('Approved', 'First Payment Approved') AND
                            '{incur_date}' BETWEEN M.`memb_eff_date`AND LAST_DAY(DATE_ADD(DATE_ADD(M.`memb_exp_date`, INTERVAL -8 MONTH), INTERVAL 1 DAY))
                        )
                        OR (
                            M.`policy_status` NOT IN ('Approved', 'First Payment Approved', 'Second payment Policy & Health Card Released') AND
                            '{incur_date}' BETWEEN LAST_DAY(DATE_ADD(DATE_ADD(M.`memb_exp_date`, INTERVAL -8 MONTH), INTERVAL 1 DAY)) AND DATE_ADD(M.`memb_exp_date`, INTERVAL -6 MONTH)
                        )
                        OR (
                            M.`policy_status` NOT IN ('Approved', 'Second Payment Approved') AND
                            '{incur_date}' BETWEEN DATE_ADD(DATE_ADD(M.`memb_exp_date`, INTERVAL -6 MONTH), INTERVAL -1 DAY) AND M.`memb_exp_date`
                        )
                    )
                ) THEN 'no_gop_direct_billing'
            ELSE 'approved'
        END `memb_status`,
        CASE
            WHEN MAX(IF(M.`op_ind` = 'No', NULL, 'Yes')) != 'Yes'
                THEN 'memb_has_no_benefit'
            WHEN (
                MAX(IF(M.`has_op_debit_note` = 'No', NULL, 'Yes')) != 'Yes'
                OR (
                    M.`payment_mode` = 'Annual' AND
                    M.`policy_status` != 'Approved'
                )
                OR (
                    M.`payment_mode` = 'Semi-Annual'
                    AND (
                        (
                            M.`policy_status` NOT IN ('Approved', 'First Payment Approved') AND
                            '{incur_date}' BETWEEN M.`memb_eff_date` AND LAST_DAY(DATE_ADD(DATE_ADD(M.`memb_exp_date`, INTERVAL -8 MONTH), INTERVAL 1 DAY))
                        )
                        OR (
                            M.`policy_status` NOT IN ('Approved', 'First Payment Approved', 'Second payment Policy & Health Card Released') AND
                            '{incur_date}' BETWEEN LAST_DAY(DATE_ADD(DATE_ADD(M.`memb_exp_date`, INTERVAL -8 MONTH), INTERVAL 1 DAY)) AND DATE_ADD(M.`memb_exp_date`, INTERVAL -6 MONTH)
                        )
                        OR (
                            `policy_status` NOT IN ('Approved', 'Second Payment Approved') AND
                            '{incur_date}' BETWEEN DATE_ADD(DATE_ADD(M.`memb_exp_date`, INTERVAL -6 MONTH), INTERVAL -1 DAY) AND M.`memb_exp_date`
                        )
                    )
                )
            ) THEN 'memb_not_paid_yet'
            ELSE NULL
        END `memb_status_note`,
        M.`insured_periods` `insured_periods`,
        M.`memb_eff_date`,
        CASE
            WHEN M.`payment_mode` = 'Annual' THEN M.`memb_exp_date`
            ELSE
                CASE
                    WHEN (
                        M.`policy_status` IN ('Second payment Policy & Health Card Released', 'Approved') AND
                        '{incur_date}' >= DATE_ADD(M.`memb_exp_date`, INTERVAL -6 MONTH)
                    ) THEN M.`memb_exp_date`
                    WHEN M.`memb_exp_date` < DATE_ADD(M.`memb_exp_date`, INTERVAL -6 MONTH)
                        THEN M.`memb_exp_date`
                    ELSE DATE_ADD(M.`memb_exp_date`, INTERVAL -6 MONTH)
                END
        END `memb_exp_date`,
        M.`term_date`,
        M.`min_memb_eff_date`,
        M.`reinst_date`,
        CASE
            WHEN M.`min_memb_eff_date` >= IFNULL(M.`reinst_date`, M.`min_memb_eff_date`)
                THEN DATE_ADD(M.`min_memb_eff_date`, INTERVAL 1 YEAR)
            ELSE DATE_ADD(M.`reinst_date`, INTERVAL 1 YEAR)
        END `special_disease_waiting_end`,
        M.`wait_period`,
        M.`spec_dis_period`,
        `mepl_incls{lang}` `included_diags`,
        CONCAT(
            IFNULL(IFNULL(`mepl_excls{lang}`, `mepl_excls`), ''), ', ',
            IFNULL(IFNULL(`plan_excls{lang}`, `plan_excls`), ''), ', ',
            IFNULL(IFNULL(`memb_rstr{lang}`, `memb_rstr`), '')
        ) `excluded_diags`,
        GROUP_CONCAT(DISTINCT
            REPLACE(
                CONCAT(
                    '• ',
                    TRIM(
                        CASE
                            WHEN M.`memb_rstr{lang}` = ''
                                OR M.`memb_rstr{lang}` IS NULL
                                THEN M.`memb_rstr`
                            ELSE M.`memb_rstr{lang}`
                        END
                    )
                ),
                '- -',
                '•'
            )
            ORDER BY M.`id`
            SEPARATOR '<br/>'
        ) `memb_event`,
        `mepl_incls{lang}` `mepl_incls`,
        `mepl_excls{lang}` `mepl_excls`,
        `plan_excls{lang}` `plan_excls`,
        M.`op_ind`,
        M.`dt_ind`,
        M.`is_renew`,
        CASE
            WHEN (
                M.`min_memb_eff_date` > ('{incur_date}' - INTERVAL 30 DAY) AND
                M.`wait_period` = 'Yes'
            ) THEN '30_day_waiting'
            ELSE NULL
        END `memb_eff_date_note`,
        CASE
            WHEN (
                M.`reinst_date` IS NOT NULL AND
                M.`reinst_date` > ('{incur_date}' - INTERVAL 9 DAY)
            ) THEN '10_day_waiting'
            ELSE NULL
        END `reinst_date_note`,
        CASE
            WHEN M.`memb_exp_date` BETWEEN '{incur_date}' AND '{incur_date}' + INTERVAL 1 week
                THEN 'card_go_expired'
            WHEN M.`memb_exp_date` < '{incur_date}'
                THEN 'card_is_expired'
            ELSE NULL
        END `memb_exp_date_note`,
        CASE
            WHEN M.`term_date` BETWEEN '{incur_date}' AND '{incur_date}' + INTERVAL 1 week
                THEN 'card_go_expired'
            WHEN M.`term_date` < '{incur_date}'
                THEN 'card_is_expired'
            ELSE NULL
        END `term_date_note`
    FROM `pcv_member` M
    WHERE TRIM(LEADING '0' FROM M.`mbr_no`) = ?
	  AND `company` = 'pcv'
      AND M.`product` = 'MD'
      AND '{incur_date}' BETWEEN M.`memb_eff_date`
                             AND IFNULL(M.`term_date`, M.`memb_exp_date`) + INTERVAL 1 DAY
    GROUP BY M.`mbr_no`
) M
;
