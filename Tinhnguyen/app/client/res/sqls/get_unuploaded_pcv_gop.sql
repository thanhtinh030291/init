SELECT
    D.`db_ref_no`,
    G.`pocy_no`,
    G.`mbr_no`,
    DATE(G.`time`) `rcv_date`,
    G.`incur_date`,
    G.`diagnosis` `diag_codes`,
    P.`code` `prov_code`,
    P.`name` `prov_name`,
    H.`code` `ben_head`,
    D.`pres_amt`,
    D.`app_amt`
FROM `pcv_history` G
    INNER JOIN `provider` P
            ON G.`provider_id` = P.`id`
    INNER JOIN `pcv_db_claim` D
            ON D.`pcv_history_id` = G.`id`
    INNER JOIN `pcv_head` H
            ON D.`pcv_head_id` = H.`id`
     LEFT JOIN `pcv_claim_line` C
            ON D.`db_ref_no` = C.`db_ref_no`
           AND C.`id` IS NULL
WHERE TRIM(LEADING '0' FROM G.`mbr_no`) = ?
    AND G.`incur_date` IS NOT NULL
    AND G.`result` IS NOT NULL
    AND G.`result` != 'null'
    AND D.`pres_amt` IS NOT NULL
    AND D.`status` NOT IN (
        'Canceled',
        'Deleted'
    )
