WITH member_plan AS (
    SELECT
        memb.memb_oid,
        mepl.mepl_oid,
        popl.popl_oid,
        popl.plan_oid,
        pocy.pocy_oid
    FROM mr_member memb
        JOIN mr_member_plan mepl
          ON mepl.memb_oid = memb.memb_oid
        JOIN mr_policy_plan popl
          ON mepl.popl_oid = popl.popl_oid
        JOIN mr_policy pocy
          ON popl.pocy_oid = pocy.pocy_oid
    WHERE TRIM(LEADING 0 FROM memb.mbr_no) = ?
      AND TO_DATE(?, 'YYYY-MM-DD') >= mepl.eff_date
      AND TO_DATE(?, 'YYYY-MM-DD') < 1 + NVL(mepl.term_date, mepl.exp_date)
      AND TO_DATE(?, 'YYYY-MM-DD') >= memb.eff_date
      AND TO_DATE(?, 'YYYY-MM-DD') < 1 + NVL(memb.term_date, mepl.exp_date)
      AND TO_DATE(?, 'YYYY-MM-DD') >= pocy.eff_date
      AND TO_DATE(?, 'YYYY-MM-DD') < 1 + NVL(pocy.term_date, pocy.exp_date)
),
benefit AS (
    {bene_sql}
),
member_limit AS (
    SELECT
        lili.memb_oid,
        lili.popl_oid,
        lili.plli_oid,
        mepl.plan_oid,
        lili.pocy_plan_desc plan,
        lili.limit_level,
        lili.scma_oid_limit_type limit_type,
        lili.limit ben_limit,
        lili.ben_type,
        lili.ben_head
    FROM vw_cl_limit_list lili
        JOIN member_plan mepl
          ON lili.memb_oid = mepl.memb_oid
         AND lili.popl_oid = mepl.popl_oid
        JOIN (
            SELECT
                fn_get_sys_code(behd.scma_oid_ben_type) ben_type,
                popl.popl_oid
            FROM mr_policy_plan popl
                JOIN mr_policy_plan_benefit pobe
                ON popl.popl_oid = pobe.popl_oid
                JOIN pd_plan_benefit plbe
                ON pobe.plbe_oid = plbe.plbe_oid
                JOIN pd_ben_head behd
                ON plbe.behd_oid = behd.behd_oid
            WHERE pobe.ben_type_ind = 'Y'
        ) pobe
          ON lili.popl_oid = pobe.popl_oid
         AND lili.ben_type || ', ' LIKE '%' || pobe.ben_type || ', %'
),
member_claim AS (
    SELECT
        clli.clli_oid,
        clli.popl_oid,
        mepl.memb_oid,
        mepl.plan_oid,
        clli.diag_oid,
        clli.proc_oid,
        clli.db_ref_no,
        diag.diag_code,
        clli.incid_date,
        clli.incur_date_from,
        clli.tooth_no,
        clli.tooth_no2,
        clli.tooth_no3,
        clli.tooth_no4,
        clli.scma_oid_product product,
        clli.trip_date_from,
        clli.trip_date_to,
        clli.symptom_date,
        behd.ben_head,
        behd.scma_oid_ben_type
    FROM cl_line clli
        JOIN rt_diagnosis diag
          ON clli.diag_oid = diag.diag_oid
        JOIN member_plan mepl
          ON clli.memb_oid = mepl.memb_oid
         AND clli.popl_oid = mepl.popl_oid
        JOIN pd_ben_head behd
          ON clli.behd_oid = behd.behd_oid
    WHERE clli.rev_date IS NULL
),
member_spent AS (
    SELECT DISTINCT
        meli.memb_oid,
        meli.popl_oid,
        meli.plli_oid,
        meli.plan,
        meli.ben_type,
        meli.ben_head,
        meli.ben_code,
        meli.ben_name,
        meli.sequence,
        meli.limit_level,
        DECODE(meli.limit_level,
            'CT', DECODE(meli.limit_type,
                'LIMIT_TYPE_A', 'LIMIT_TYPE_M',
                'LIMIT_TYPE_G', NULL,
                meli.limit_type
            ),
            'T', DECODE(meli.limit_type,
                'LIMIT_TYPE_A', 'LIMIT_TYPE_M',
                meli.limit_type
            ),
            'CH', DECODE(meli.limit_type,
                'LIMIT_TYPE_G', NULL,
                meli.limit_type
            ),
            meli.limit_type
        ) limit_type,
        meli.ben_limit,
        mecl.diag_oid,
        mecl.diag_code,
        CL_COMMON_BAT_PKG.GetUsedBenefit(
            meli.popl_oid,
            meli.memb_oid,
            meli.plli_oid,
            meli.limit_type,
            mecl.diag_oid,
            mecl.proc_oid,
            mecl.incid_date,
            mecl.incur_date_from,
            mecl.tooth_no,
            mecl.tooth_no2,
            mecl.tooth_no3,
            mecl.tooth_no4,
            mecl.product,
            mecl.trip_date_from,
            mecl.trip_date_to,
            mecl.symptom_date,
            mecl.ben_head,
            mecl.scma_oid_ben_type
        ) ben_spent
    FROM (
        SELECT
            bene.name ben_name,
            bene.code ben_code,
            bene.sequence,
            meli.*
        FROM benefit bene
            JOIN (
                SELECT * FROM member_limit
                UNION
                SELECT
                    memb_oid,
                    popl_oid,
                    plli_oid,
                    plan_oid,
                    plan,
                    limit_level,
                    'LIMIT_TYPE_B' limit_type,
                    null ben_limit,
                    ben_type,
                    ben_head
                FROM member_limit
                WHERE ben_type = 'IP'
                  AND ben_head IS NULL
                UNION
                SELECT
                    memb_oid,
                    popl_oid,
                    plli_oid,
                    plan_oid,
                    plan,
                    limit_level,
                    'LIMIT_TYPE_B' limit_type,
                    null ben_limit,
                    ben_type,
                    ben_head
                FROM member_limit
                WHERE ben_type = 'OP'
                  AND ben_head IS NULL
                UNION
                SELECT
                    memb_oid,
                    popl_oid,
                    plli_oid,
                    plan_oid,
                    plan,
                    limit_level,
                    'LIMIT_TYPE_B' limit_type,
                    null ben_limit,
                    ben_type,
                    ben_head
                FROM member_limit
                WHERE ben_type = 'DT'
                  AND ben_head IS NULL
            ) meli
              ON bene.type = meli.ben_type
             AND (
                    (meli.ben_head IS NOT NULL AND bene.code || ', ' LIKE '%' || meli.ben_head || ', %') OR
                    (meli.ben_head IS NULL AND bene.code = bene.type)
               )
    ) meli
         LEFT JOIN member_claim mecl
                ON meli.plan_oid = mecl.plan_oid
),
member_request AS (
    SELECT mere.*
    FROM (
        {gop_sql}
    ) mere
         LEFT JOIN member_claim mecl
                ON mere.id = mecl.db_ref_no
    WHERE mecl.db_ref_no IS NULL
)
SELECT
    plan "plan",
    sequence "sequence",
    ben_type "ben_type",
    ben_name "ben_desc",
    amt_vis_limit "amt_vis_limit",
    amt_vis_used "amt_vis_used",
    amt_day_limit "amt_day_limit",
    amt_day_used "amt_day_used",
    amt_yr_limit "amt_yr_limit",
    amt_yr_used "amt_yr_used",
    amt_life_limit "amt_life_limit",
    amt_life_used "amt_life_used",
    amt_dis_vis_limit "amt_dis_vis_limit",
    amt_dis_vis_used "amt_dis_vis_used",
    amt_dis_yr_limit "amt_dis_yr_limit",
    amt_dis_yr_used "amt_dis_yr_used",
    amt_dis_life_limit "amt_dis_life_limit",
    amt_dis_life_used "amt_dis_life_used",
    vis_day_limit "vis_day_limit",
    vis_day_used "vis_day_used",
    vis_day_yr_limit "vis_day_yr_limit",
    vis_day_yr_used "vis_day_yr_used",
    day_dis_yr_limit "day_dis_yr_limit",
    day_dis_yr_used "day_dis_yr_used",
    ded_amt_limit "ded_amt_limit",
    ded_amt_used "ded_amt_used"
FROM (
    SELECT * FROM (
        SELECT DISTINCT
            mesp.plan,
            mesp.ben_name,
            mesp.ben_type,
            mesp.ben_code,
            mesp.sequence,
            mesp.limit_type,
            mesp.ben_limit,
            CASE
                WHEN mesp.limit_type IN (
                    'LIMIT_TYPE_E',
                    'LIMIT_TYPE_F',
                    'LIMIT_TYPE_G',
                    'LIMIT_TYPE_H'
                )
                    THEN MAX(NVL(mesp.ben_spent, 0)) + 1
                ELSE MAX(NVL(mesp.ben_spent, 0)) + SUM(NVL(mere.amount, 0))
            END used_amount
        FROM (
            SELECT
                plan,
                ben_name,
                ben_type,
                ben_code,
                sequence,
                limit_type,
                ben_limit,
                MAX(NVL(ben_spent, 0)) ben_spent
            FROM member_spent
            GROUP BY
                plan,
                ben_name,
                ben_type,
                ben_code,
                sequence,
                limit_type,
                ben_limit
        ) mesp
             LEFT JOIN member_request mere
                    ON mere.ben_desc = mesp.ben_name
                    OR (mesp.ben_type = mere.ben_type AND mesp.ben_code = mesp.ben_type)
        GROUP BY
            mesp.plan,
            mesp.ben_name,
            mesp.ben_type,
            mesp.ben_code,
            mesp.sequence,
            mesp.limit_type,
            mesp.ben_limit,
            mesp.ben_spent
    )
    PIVOT (
        MAX(ben_limit) limit,
        MAX(used_amount) used
        FOR limit_type IN (
            'LIMIT_TYPE_A' amt_vis,
            'LIMIT_TYPE_B' amt_yr,
            'LIMIT_TYPE_C' amt_dis_yr,
            'LIMIT_TYPE_D' amt_life,
            'LIMIT_TYPE_E' vis_day_yr,
            'LIMIT_TYPE_F' vis_day,
            'LIMIT_TYPE_G' ded_amt,
            'LIMIT_TYPE_H' day_dis_yr,
            'LIMIT_TYPE_I' amt_day,
            'LIMIT_TYPE_L' amt_dis_vis,
            'LIMIT_TYPE_M' amt_dis_life
        )
    )
    ORDER BY
        plan,
        sequence
)
