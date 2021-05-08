SELECT
    M.`mbr_no`,
    M.`memb_ref_no`,
    M.`dob`,
    M.`mbr_name`,
    M.`pocy_no`,
    M.`plan_desc`,
    M.`payment_mode`,
    M.`memb_status`,
    M.`memb_status_note`,
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
    '' `db_ref_no`
FROM (
    SELECT DISTINCT
        `mbr_no`,
        `memb_ref_no`,
        `dob`,
        CONCAT(M.`mbr_last_name`, ' ', M.`mbr_first_name`) `mbr_name`,
        `pocy_ref_no` `pocy_no`,
        GROUP_CONCAT(DISTINCT `plan_desc` SEPARATOR ';;;') `plan_desc`,
        CASE
            WHEN `payment_mode` = 'Semi-Annual'
                THEN 'semi_annual'
            WHEN `payment_mode` = 'Annual'
                THEN 'annual'
            ELSE `payment_mode`
        END `payment_mode`,
        CASE
            WHEN (`min_memb_eff_date` > '{incur_date}' - INTERVAL 30 DAY AND `wait_period` = 'Yes')
              OR (`reinst_date` IS NOT NULL AND `reinst_date` > '{incur_date}' - INTERVAL 9 DAY)
              OR IFNULL(`term_date`, `memb_exp_date`) < '{incur_date}'
              OR MAX(IF(`has_op_debit_note` = 'No', NULL, 'Yes')) != 'Yes'
                THEN 'no_gop_direct_billing'
            ELSE 'approved'
        END `memb_status`,
        CASE
            WHEN MAX(IF(`op_ind` = 'No', NULL, 'Yes')) != 'Yes'
                THEN 'memb_has_no_cathay_benefit'
            WHEN  MAX(IF(`has_op_debit_note` = 'No', NULL, 'Yes')) != 'Yes'
                THEN 'memb_not_paid_yet'
            ELSE NULL
        END `memb_status_note`,
        `insured_periods` `insured_periods`,
        `memb_eff_date`,
        `memb_exp_date`,
        `term_date`,
        `min_memb_eff_date`,
        `reinst_date`,
        CASE
            WHEN `min_memb_eff_date` >= IFNULL(`reinst_date`, `min_memb_eff_date`)
                THEN DATE_ADD(`min_memb_eff_date`, INTERVAL 1 YEAR)
            ELSE DATE_ADD(`reinst_date`, INTERVAL 1 YEAR)
        END `special_disease_waiting_end`,
        `wait_period`,
        `spec_dis_period`,
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
                            WHEN `memb_rstr{lang}` = '' OR `memb_rstr{lang}` IS NULL
                                THEN `memb_rstr`
                            ELSE `memb_rstr{lang}`
                        END
                    )
                ),
                '- -',
                '•'
            )
            ORDER BY `id`
            SEPARATOR '<br/>'
        ) `memb_event`,
        `is_renew`,
        CASE
            WHEN (`min_memb_eff_date` > ('{incur_date}' - INTERVAL 30 DAY) AND `wait_period` = 'Yes')
                THEN '30_day_waiting'
            ELSE NULL
        END `memb_eff_date_note`,
        CASE
            WHEN (`reinst_date` IS NOT NULL AND `reinst_date` > ('{incur_date}' - INTERVAL 9 DAY))
                THEN '10_day_waiting'
            ELSE NULL
        END `reinst_date_note`,
        CASE
            WHEN `memb_exp_date` BETWEEN '{incur_date}' AND '{incur_date}' + INTERVAL 1 week
                THEN 'card_go_expired'
            WHEN `memb_exp_date` < '{incur_date}'
                THEN 'card_is_expired'
            ELSE NULL
        END `memb_exp_date_note`,
        CASE
            WHEN `term_date` BETWEEN '{incur_date}' AND '{incur_date}' + INTERVAL 1 week
                THEN 'card_go_expired'
            WHEN `term_date` < '{incur_date}'
                THEN 'card_is_expired'
            ELSE NULL
        END `term_date_note`
    FROM `cathay_member`
    WHERE TRIM(LEADING '0' FROM `mbr_no`) = ?
	  AND `company` = 'cathay'
      AND `product` = 'MD'
      AND '{incur_date}' BETWEEN `memb_eff_date` AND IFNULL(`term_date`, `memb_exp_date`) + INTERVAL 1 DAY
    GROUP BY `mbr_no`
) M
;
