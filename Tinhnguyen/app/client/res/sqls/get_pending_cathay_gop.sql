SELECT
    D.`id`,
    D.`db_ref_no`,
    H.`ben_heads` `ben_head`,
    B.`ben_type`,
    B.`ben_desc{lang_code}` `ben_desc`,
    G.`diagnosis`,
    D.`pres_amt`,
    D.`app_amt`
FROM `cathay_history` G
    INNER JOIN `cathay_db_claim` D
            ON D.`cathay_history_id` = G.`id`
    INNER JOIN `cathay_head` H
            ON D.`cathay_head_id` = H.`id`
    INNER JOIN `cathay_benefit` B
            ON H.`cathay_benefit_id` = B.`id`
     LEFT JOIN `cathay_claim_line` C
            ON D.`db_ref_no` = C.`db_ref_no`
           AND C.`id` IS NULL
WHERE TRIM(LEADING '0' FROM G.`mbr_no`) = ?
  AND G.`incur_date` BETWEEN ? AND ? + INTERVAL 1 DAY
  AND D.`pres_amt` IS NOT NULL
  AND D.`status` NOT IN (
        'Canceled',
        'Deleted'
    )
