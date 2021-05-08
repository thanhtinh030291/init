SELECT
    G.`id`,
    G.`db_ref_no`,
    G.`email`,
    G.`ip_address`,
    G.`time`,
    DATE_FORMAT(G.`time`, '%Y-%m-%d %H:%i:%s.%f') `time2`,
    G.`prov_name`,
    G.`diag_name`,
    G.`note`,
    G.`call_time`,
    G.`tel_no`,
    G.`benefit`,
    G.`incur_date`,
    G.`pres_amt`,
    IFNULL(C.`app_amt`, G.`app_amt`) `app_amt`,
    G.`pocy_no`,
    G.`result`,
    CASE
        WHEN C.`app_amt` IS NOT NULL
            THEN 'FC'
        WHEN G.`status` = 'Accepted'
            THEN 'AC'
        WHEN G.`status` = 'Rejected'
            THEN 'RJ'
        WHEN G.`status` = 'Confirmed'
            THEN 'CF'
        WHEN G.`status` = 'Canceled'
            THEN 'CC'
        WHEN G.`status` = 'Deleted'
            THEN 'DL'
    END `status`,
    CASE
        WHEN C.`app_amt` IS NULL
            THEN CASE
                WHEN G.`status` = 'Accepted'
                    THEN '{accepted}'
                WHEN G.`status` = 'Rejected'
                    THEN '{rejected}'
                WHEN G.`status` = 'Confirmed'
                    THEN '{confirmed}'
                WHEN G.`status` = 'Canceled'
                    THEN '{canceled}'
                WHEN G.`status` = 'Deleted'
                    THEN '{deleted}'
            END
        WHEN C.`app_amt` = 0
            THEN '{rejected}'
        WHEN C.`app_amt` = C.`pres_amt`
            THEN '{accepted}'
        ELSE '{partially}'
    END `status_desc`,
    C.`eff_date`,
    C.`exp_date`,
    C.`term_date`
FROM (
    SELECT
        D.`id`,
        D.`db_ref_no`,
        U.`email`,
        G.`ip_address`,
        G.`time`,
        P.`name` `prov_name`,
        G.`diagnosis` `diag_name`,
        G.`note`,
        G.`call_time`,
        G.`tel_no`,
        B.`ben_desc{lang}` `benefit`,
        G.`incur_date`,
        G.`mbr_no`,
        G.`pocy_no`,
        G.`result`,
        D.`pres_amt`,
        D.`app_amt`,
        D.`status`
    FROM `pcv_history` G
        JOIN `user` U
          ON G.`email` = U.`email`
        JOIN `provider` P
          ON G.`provider_id` = P.`id`
        JOIN `pcv_db_claim` D
          ON D.`pcv_history_id` = G.`id`
        JOIN `pcv_head` H
          ON D.`pcv_head_id` = H.`id`
        JOIN `pcv_benefit` B
          ON H.`pcv_benefit_id` = B.`id`
    WHERE TRIM(LEADING '0' FROM G.`mbr_no`) = ?
      AND G.`incur_date` IS NOT NULL
      AND G.`result` IS NOT NULL
      AND G.`result` != 'null'
      AND D.`pres_amt` IS NOT NULL
) G
LEFT JOIN (
    SELECT
        C.`db_ref_no`,
        C.`cl_no`,
        C.`incur_date_from` `incur_date`,
        SUM(C.`pres_amt`) `pres_amt`,
        SUM(
            CASE C.`status`
                WHEN 'AC' THEN C.`app_amt`
                WHEN 'RJ' THEN 0
                ELSE NULL
            END
        ) `app_amt`,
        M.`eff_date`,
        M.`exp_date`,
        M.`term_date`
    FROM `pcv_claim_line` C
        JOIN (
            SELECT DISTINCT
                `mbr_no`,
                `memb_eff_date` `eff_date`,
                `memb_exp_date` `exp_date`,
                `term_date` `term_date`
            FROM `hbs_member`
            WHERE TRIM(LEADING '0' FROM `mbr_no`) = ?
			  AND `company` = 'pcv'
        ) M
          ON M.`mbr_no` = C.`mbr_no`
         AND M.`eff_date` = C.`memb_eff_date`
    GROUP BY
        C.`db_ref_no`,
        C.`cl_no`,
        C.`incur_date_from`,
        M.`eff_date`,
        M.`exp_date`,
        M.`term_date`
) C
ON G.`db_ref_no` = C.`db_ref_no`
