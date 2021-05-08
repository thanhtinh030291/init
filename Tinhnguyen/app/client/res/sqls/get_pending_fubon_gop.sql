SELECT
    D.`id`,
    D.`db_ref_no`,
    H.`ben_heads` `ben_head`,
    B.`ben_type`,
    B.`ben_desc{lang_code}` `ben_desc`,
    G.`diagnosis`,
    D.`pres_amt`,
    D.`app_amt`
FROM `fubon_history` G
    INNER JOIN `fubon_db_claim` D
            ON D.`fubon_history_id` = G.`id`
    INNER JOIN `fubon_head` H
            ON D.`fubon_head_id` = H.`id`
    INNER JOIN `fubon_benefit` B
            ON H.`fubon_benefit_id` = B.`id`
     LEFT JOIN `fubon_claim_line` C
            ON D.`db_ref_no` = C.`db_ref_no`
           AND C.`id` IS NULL
WHERE TRIM(LEADING '0' FROM G.`mbr_no`) = ?
  AND G.`incur_date` BETWEEN ? AND ? + INTERVAL 1 DAY
  AND D.`pres_amt` IS NOT NULL
  AND D.`status` NOT IN (
        'Canceled',
        'Deleted'
    )
