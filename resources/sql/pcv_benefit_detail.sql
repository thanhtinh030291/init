WITH detail_a AS (
    SELECT
        pp.plan_oid,
        100 - NVL(MAX(plli.copay_pct), 0) || '%' "copay",
        TRIM(LISTAGG(plli.deduct_amt, ' ') WITHIN GROUP(ORDER BY pp.plan_oid, plli.limit_type)) "dedAmt",
        TRIM(LISTAGG(plli.amt_day, ' ') WITHIN GROUP(ORDER BY pp.plan_oid, plli.limit_type)) "amtperday"
    FROM pd_plan pp
        JOIN pd_plan_limit plli
          ON pp.plan_oid = plli.plan_oid
        JOIN pd_plan_benefit pdb
          ON plli.plli_oid = pdb.plli_oid
        JOIN pd_ben_head behd
          ON pdb.behd_oid = behd.behd_oid
         AND behd.scma_oid_ben_type = 'BENEFIT_TYPE_IP'
    WHERE plli.limit_type = 'T'
       OR ( plli.limit_type = 'H' AND behd.ben_head = 'RB' )
    GROUP BY pp.plan_oid
), detail_b AS (
    SELECT
        plan_oid,
        limit1 AS "amtpervis"
    FROM (
        SELECT
            plli.amt_life limit1,
            pp.plan_oid
        FROM pd_plan pp
            JOIN pd_plan_limit plli
              ON pp.plan_oid = plli.plan_oid
             AND plli.limit_type = 'H'
             AND plli.amt_life IS NOT NULL
            JOIN pd_plan_benefit pdb
              ON plli.plli_oid = pdb.plli_oid
            JOIN pd_ben_head behd
              ON pdb.behd_oid = behd.behd_oid
             AND behd.scma_oid_ben_type = 'BENEFIT_TYPE_IP'
             AND behd.ben_head = 'SUR'
        UNION ALL
        SELECT
            plli.amt_yr limit1,
            pp.plan_oid
        FROM pd_plan pp
            JOIN pd_plan_limit plli
              ON pp.plan_oid = plli.plan_oid
             AND plli.limit_type = 'H'
             AND plli.amt_yr IS NOT NULL
            JOIN pd_plan_benefit pdb
              ON plli.plli_oid = pdb.plli_oid
            JOIN pd_ben_head behd
              ON pdb.behd_oid = behd.behd_oid
             AND behd.scma_oid_ben_type = 'BENEFIT_TYPE_IP'
             AND behd.ben_head = 'SUR'
        UNION ALL
        SELECT
            plli.amt_dis_yr limit1,
            pp.plan_oid
        FROM pd_plan pp
            JOIN pd_plan_limit plli
              ON pp.plan_oid = plli.plan_oid
             AND plli.limit_type = 'H'
             AND plli.amt_dis_yr IS NOT NULL
            JOIN pd_plan_benefit pdb
              ON plli.plli_oid = pdb.plli_oid
            JOIN pd_ben_head behd
              ON pdb.behd_oid = behd.behd_oid
             AND behd.scma_oid_ben_type = 'BENEFIT_TYPE_IP'
             AND behd.ben_head = 'SUR'
        UNION ALL
        SELECT
            plli.amt_vis   limit1,
            pp.plan_oid
        FROM pd_plan pp
            JOIN pd_plan_limit plli
              ON pp.plan_oid = plli.plan_oid
             AND plli.limit_type = 'H'
             AND plli.amt_vis IS NOT NULL
            JOIN pd_plan_benefit pdb
              ON plli.plli_oid = pdb.plli_oid
            JOIN pd_ben_head behd
              ON pdb.behd_oid = behd.behd_oid
             AND behd.scma_oid_ben_type = 'BENEFIT_TYPE_IP'
             AND behd.ben_head = 'SUR'
    )
    WHERE limit1 IS NOT NULL
),
detail AS (
    SELECT
        dta."copay",
        dta."dedAmt",
        dta."amtperday",
        dtb."amtpervis"
    FROM detail_a dta
        JOIN detail_b dtb USING ( plan_oid )
    WHERE plan_oid = ?
)
SELECT *
FROM detail