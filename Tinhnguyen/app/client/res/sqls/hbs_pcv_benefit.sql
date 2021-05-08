WITH member_plan AS (
    SELECT
        memb.memb_oid,
        mepl.mepl_oid,
        popl.popl_oid,
        popl.plan_oid,
        pocy.pocy_oid,
        CASE
            WHEN pocy.eff_date < mepl.eff_date
                THEN mepl.eff_date
            ELSE pocy.eff_date
        END eff_date,
        CASE
            WHEN pocy.exp_date < mepl.exp_date
             AND pocy.scma_oid_payment_mode != 'PAYMENT_MODE_S'
                THEN mepl.exp_date
            ELSE pocy.exp_date
        END exp_date
    FROM mr_member memb
        JOIN mr_member_plan mepl
          ON mepl.memb_oid = memb.memb_oid
        JOIN mr_policy_plan popl
          ON mepl.popl_oid = popl.popl_oid
        JOIN mr_policy pocy
          ON popl.pocy_oid = pocy.pocy_oid
    WHERE TRIM(LEADING 0 FROM memb.mbr_no) = ?
      AND pocy.proforma_ind = 'N'
      AND (
            (
                ADD_MONTHS(CURRENT_DATE, -12) >= mepl.eff_date AND
                CURRENT_DATE < 1 +NVL(mepl.term_date, mepl.exp_date)
            ) OR
            (
                CURRENT_DATE >= mepl.eff_date AND
                CURRENT_DATE < 1 + NVL(mepl.term_date, mepl.exp_date) AND
                CURRENT_DATE >= pocy.eff_date AND
                CURRENT_DATE < 1 + NVL(pocy.term_date, pocy.exp_date) AND
                CURRENT_DATE >= memb.eff_date AND
                CURRENT_DATE < 1 + NVL(memb.term_date, mepl.exp_date)
            )
        )
),
member_limit AS (
    SELECT
        lili.memb_oid,
        mepl.mepl_oid,
        lili.popl_oid,
        lili.plli_oid,
        mepl.plan_oid,
        mepl.eff_date,
        mepl.exp_date,
        lili.pocy_plan_desc plan,
        lili.limit_level,
        DECODE(lili.limit_level,
            'CT', DECODE(lili.scma_oid_limit_type,
                'LIMIT_TYPE_A', 'LIMIT_TYPE_M',
                'LIMIT_TYPE_G', NULL,
                lili.scma_oid_limit_type
            ),
            'T', DECODE(lili.scma_oid_limit_type,
                'LIMIT_TYPE_A', 'LIMIT_TYPE_M',
                lili.scma_oid_limit_type
            ),
            'CH', DECODE(lili.scma_oid_limit_type,
                'LIMIT_TYPE_G', NULL,
                lili.scma_oid_limit_type
            ),
            lili.scma_oid_limit_type
        ) limit_type,
        lili.limit ben_limit,
        lili.ben_type,
        lili.ben_head
    FROM vw_cl_limit_list lili
        INNER JOIN member_plan mepl
                ON lili.memb_oid = mepl.memb_oid
               AND lili.popl_oid = mepl.popl_oid
         LEFT JOIN mr_policy_plan_benefit pobe
                ON lili.popl_oid = pobe.popl_oid
         LEFT JOIN pd_plan_benefit plbe
                ON pobe.plbe_oid = plbe.plbe_oid
         LEFT JOIN pd_ben_head behd
                ON plbe.behd_oid = behd.behd_oid
               AND lili.ben_type = FN_GET_SYS_CODE(behd.scma_oid_ben_type)
               AND NVL(lili.ben_head, '-') LIKE  '%' || NVL(behd.ben_head, '-') || '%'
    WHERE pobe.ben_type_ind = 'Y'
),
member_claim AS (
    SELECT
        mepl.mepl_oid,
        clli.diag_oid,
        clli.proc_oid,
        clli.incid_date,
        clli.incur_date_from,
        clli.tooth_no,
        clli.tooth_no2,
        clli.tooth_no3,
        clli.tooth_no4,
        clli.scma_oid_product product,
        clli.trip_date_from,
        clli.trip_date_to,
        clli.symptom_date
    FROM cl_line clli
        JOIN member_plan mepl
          ON clli.memb_oid = mepl.memb_oid
         AND clli.popl_oid = mepl.popl_oid
    WHERE clli.rev_date IS NULL
),
member_spent AS (
    SELECT DISTINCT
        meli.memb_oid,
        meli.popl_oid,
        meli.plli_oid,
        meli.eff_date,
        meli.exp_date,
        meli.plan,
        meli.ben_type,
        meli.ben_head,
        meli.limit_level,
        meli.limit_type,
        meli.ben_limit,
        CASE
            WHEN meli.limit_type IN ('LIMIT_TYPE_C', 'LIMIT_TYPE_H', 'LIMIT_TYPE_M')
                THEN mecl.diag_oid
        END diag_oid,
        CASE
            WHEN meli.limit_type IN ('LIMIT_TYPE_A', 'LIMIT_TYPE_L')
                THEN 0
            ELSE CL_COMMON_BAT_PKG.GetUsedBenefit(
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
                mecl.symptom_date
            )
        END ben_spent
    FROM member_limit meli
        LEFT JOIN member_claim mecl
               ON meli.mepl_oid = mecl.mepl_oid
),
member_benefit AS (
    SELECT * FROM (
        SELECT DISTINCT
            memb_oid,
            popl_oid,
            plli_oid,
            eff_date,
            exp_date,
            plan,
            ben_type,
            ben_head,
            diag_oid,
            limit_level,
            limit_type,
            ben_limit,
            ben_spent
        FROM member_spent
    )
    PIVOT (
        MAX(ben_limit) limit,
        MAX(ben_spent) spent
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
)
SELECT
    TO_CHAR(eff_date, 'YYYY-MM-DD') "eff_date",
    TO_CHAR(exp_date, 'YYYY-MM-DD') "exp_date",
    plan "plan",
    ben_type "ben_type",
    ben_head "ben_head",
    diag_oid "diag_oid",
    diag_code "diag_code",
    diag_desc "diag_desc",
    diag_desc_vi "diag_desc_vi",
    limit_level "limit_level",
    MAX(amt_vis_limit) "amt_vis_limit",
    MAX(amt_yr_limit) "amt_yr_limit",
    MAX(amt_dis_yr_limit) "amt_dis_yr_limit",
    MAX(amt_life_limit) "amt_life_limit",
    MAX(vis_day_yr_limit) "vis_day_yr_limit",
    MAX(vis_day_limit) "vis_day_limit",
    MAX(ded_amt_limit) "ded_amt_limit",
    MAX(day_dis_yr_limit) "day_dis_yr_limit",
    MAX(amt_day_limit) "amt_day_limit",
    MAX(amt_dis_vis_limit) "amt_dis_vis_limit",
    MAX(amt_dis_life_limit) "amt_dis_life_limit",
    MAX(amt_vis_spent) "amt_vis_spent",
    MAX(amt_yr_spent) "amt_yr_spent",
    MAX(amt_dis_yr_spent) "amt_dis_yr_spent",
    MAX(amt_life_spent) "amt_life_spent",
    MAX(vis_day_yr_spent) "vis_day_yr_spent",
    MAX(vis_day_spent) "vis_day_spent",
    MAX(ded_amt_spent) "ded_amt_spent",
    MAX(day_dis_yr_spent) "day_dis_yr_spent",
    MAX(amt_day_spent) "amt_day_spent",
    MAX(amt_dis_vis_spent) "amt_dis_vis_spent",
    MAX(amt_dis_life_spent) "amt_dis_life_spent"
FROM (
    SELECT DISTINCT
        eff_date,
        exp_date,
        plan,
        ben_type,
        COALESCE(ben_head, ben_type) ben_head,
        NULL diag_oid,
        NULL diag_code,
        NULL diag_desc,
        NULL diag_desc_vi,
        limit_level,
        amt_vis_limit,
        amt_yr_limit,
        amt_dis_yr_limit,
        amt_life_limit,
        vis_day_yr_limit,
        vis_day_limit,
        ded_amt_limit,
        day_dis_yr_limit,
        amt_day_limit,
        amt_dis_vis_limit,
        amt_dis_life_limit,
        amt_vis_spent,
        COALESCE(
            amt_yr_spent,
            CL_COMMON_BAT_PKG.GetUsedBenefit(
                popl_oid,
                memb_oid,
                plli_oid,
                'LIMIT_TYPE_B',
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL
            )
        ) amt_yr_spent,
        NULL amt_dis_yr_spent,
        COALESCE(
            amt_yr_spent,
            CL_COMMON_BAT_PKG.GetUsedBenefit(
                popl_oid,
                memb_oid,
                plli_oid,
                'LIMIT_TYPE_D',
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL
            )
        ) amt_life_spent,
        vis_day_yr_spent,
        vis_day_spent,
        NULL ded_amt_spent,
        NULL day_dis_yr_spent,
        amt_day_spent amt_day_spent,
        NULL amt_dis_vis_spent,
        NULL amt_dis_life_spent
    FROM member_benefit
    UNION ALL
    SELECT DISTINCT
        mebe.eff_date,
        mebe.exp_date,
        mebe.plan,
        mebe.ben_type,
        COALESCE(
            mebe.ben_head,
            mebe.ben_type
        ),
        mebe.diag_oid,
        diag.diag_code,
        diag.diag_desc,
        diag.diag_desc_vn,
        mebe.limit_level,
        NULL amt_vis_limit,
        NULL amt_yr_limit,
        mebe.amt_dis_yr_limit,
        NULL amt_life_limit,
        NULL vis_day_yr_limit,
        NULL vis_day_limit,
        NULL ded_amt_limit,
        mebe.day_dis_yr_limit,
        NULL amt_day_limit,
        mebe.amt_dis_vis_limit,
        mebe.amt_dis_life_limit,
        NULL amt_vis_spent,
        NULL amt_yr_spent,
        mebe.amt_dis_yr_spent,
        NULL amt_life_spent,
        NULL vis_day_yr_spent,
        NULL vis_day_spent,
        NULL ded_amt_spent,
        mebe.day_dis_yr_spent,
        NULL amt_day_spent,
        mebe.amt_dis_vis_spent,
        mebe.amt_dis_life_spent
    FROM member_benefit mebe
        JOIN rt_diagnosis diag
          ON mebe.diag_oid = diag.diag_oid
)
GROUP BY
    eff_date,
    exp_date,
    plan,
    ben_type,
    ben_head,
    diag_oid,
    diag_code,
    diag_desc,
    diag_desc_vi,
    limit_level