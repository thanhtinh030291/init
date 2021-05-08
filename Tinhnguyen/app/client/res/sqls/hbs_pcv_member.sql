WITH members AS
(
    SELECT
        memb.memb_oid,
        memb.mbr_no,
        memb.mbr_last_name || ' ' || memb.mbr_first_name mbr_name,
        memb.dob,
        FN_GET_SYS_CODE(scma_oid_sex) gender,
        memb.corr_addr_1 || ', ' || memb.corr_addr_2 || ', ' || memb.corr_addr_3 || ', ' || memb.corr_addr_4 address,
        FN_GET_SYS_CODE_DESC(memb.scma_oid_yn_wait_period, 'en') has_wait_period,
        NVL(FN_GET_SYS_CODE_DESC(memb.scma_oid_yn_spec_dis_period, 'en'), 'Yes') has_spec_dis_period,
        memb.term_date,
        mepl.mepl_oid,
        mepl.reinst_date,
        popl.popl_oid,
        plan.plan_oid,
        pocy.pocy_no,
        pocy.pocy_ref_no,
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
        END exp_date,
        FN_GET_SYS_CODE_DESC(pocy.scma_oid_payment_mode, 'en') payment_mode,
        FN_GET_SYS_CODE_DESC(pocy.scma_oid_pocy_status, 'en') policy_status,
        FN_GET_SYS_CODE(prty.scma_oid_product) product,
        DECODE(
            prty.scma_oid_product,
            'PRODUCT_TV_BV', plan.plan_desc,
            popl.pocy_plan_desc
        ) plan_desc
    FROM mr_member memb
        JOIN mr_member_plan mepl
          ON memb.memb_oid = mepl.memb_oid
        JOIN mr_policy_plan popl
          ON mepl.popl_oid = popl.popl_oid
        JOIN mr_policy pocy
          ON popl.pocy_oid = pocy.pocy_oid
        JOIN pd_plan plan
          ON popl.plan_oid = plan.plan_oid
        JOIN rt_product_type prty
          ON plan.prty_oid = prty.prty_oid
    WHERE prty.scma_oid_product = 'PRODUCT_MD'
      AND pocy.proforma_ind = 'N'
      AND mepl.status IS NULL
      AND TRIM(LEADING 0 FROM memb.mbr_no) = ?
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
    ORDER BY eff_date
),
member_eff_dates AS
(
    SELECT
        memb_oid,
        eff_date,
        exp_date,
        last_year,
        NVL(LAG(last_year) OVER (PARTITION BY memb_oid ORDER BY eff_date), 1) first_year
    FROM (
        SELECT
            memb_oid,
            eff_date,
            exp_date,
            DECODE(exp_date + 1 - LEAD(eff_date) OVER (PARTITION BY memb_oid ORDER BY eff_date), 0, 0, 1) last_year
        FROM (
            SELECT DISTINCT
                mepl.memb_oid,
                mepl.eff_date,
                NVL(mepl.term_date, mepl.exp_date) exp_date
            FROM mr_member_plan mepl
                JOIN mr_policy_plan popl
                  ON mepl.popl_oid = popl.popl_oid
                JOIN mr_policy pocy
                  ON popl.pocy_oid = pocy.pocy_oid
            WHERE pocy.proforma_ind = 'N'
              AND mepl.status IS NULL
              AND mepl.memb_oid IN (SELECT DISTINCT memb_oid FROM members)
        )
    )
)
SELECT DISTINCT
    TRIM(memb.mbr_name) "mbr_name",
    TO_CHAR(memb.dob, 'YYYY-MM-DD') "dob",
    memb.gender "gender",
    REGEXP_REPLACE(REPLACE(REPLACE(memb.address, ', , ', ', '), '  ', ' '), '\s*,\s*$', '') "address",
    memb.pocy_no "pocy_no",
    memb.pocy_ref_no "pocy_ref_no",
    memb.mbr_no "mbr_no",
    memb.mepl_oid "mepl_oid",
    memb.payment_mode "payment_mode",
    TO_CHAR(memb.eff_date, 'YYYY-MM-DD') "memb_eff_date",
    TO_CHAR(memb.exp_date, 'YYYY-MM-DD') "memb_exp_date",
    TO_CHAR(memb.term_date, 'YYYY-MM-DD') "term_date",
    TO_CHAR(mefd.min_eff_date, 'YYYY-MM-DD') "min_memb_eff_date",
    TO_CHAR(pocy.min_eff_date, 'YYYY-MM-DD') "min_pocy_eff_date",
    mefp.insured_periods "insured_periods",
    memb.has_wait_period "wait_period",
    memb.has_spec_dis_period "spec_dis_period",
    memb.product "product",
    memb.plan_oid "plan_oid",
    memb.plan_desc "plan_desc",
    TRIM(mers.rstr_desc) "memb_rstr",
    TRIM(mers.rstr_desc_vn) "memb_rstr_vi",
    TO_CHAR(memb.reinst_date, 'YYYY-MM-DD') "reinst_date",
    memb.policy_status "policy_status",
    DECODE(mefd.min_eff_date, memb.eff_date, 'No', 'Yes') "is_renew"
FROM members memb
    INNER JOIN (
                SELECT
                    memb_oid,
                    MAX(eff_date) KEEP (DENSE_RANK LAST ORDER BY first_year NULLS FIRST) min_eff_date
                FROM member_eff_dates
                GROUP BY memb_oid
             ) mefd
            ON mefd.memb_oid = memb.memb_oid
    INNER JOIN (
                SELECT
                    memb_oid,
                    RTRIM(LISTAGG(period_part) WITHIN GROUP (ORDER BY row_id), ';') insured_periods
                FROM (
                    SELECT
                        ROWNUM row_id,
                        memb_oid,
                        CASE
                            WHEN first_year = 1 AND last_year = 1
                                THEN TO_CHAR(eff_date, 'YYYY-MM-DD') || ' - ' || TO_CHAR(exp_date, 'YYYY-MM-DD') || ', '
                            WHEN first_year = 1 AND last_year = 0
                                THEN TO_CHAR(eff_date, 'YYYY-MM-DD') || ' - '
                            WHEN first_year = 0 AND last_year = 1
                                THEN TO_CHAR(exp_date, 'YYYY-MM-DD') || ', '
                        END period_part
                    FROM member_eff_dates
                    WHERE first_year + last_year > 0
                    ORDER BY period_part ASC
                )
                GROUP BY memb_oid
             ) mefp
            ON mefp.memb_oid = memb.memb_oid
    INNER JOIN (
                SELECT
                    pocy_no,
                    MIN(eff_date) min_eff_date
                FROM mr_policy
                WHERE status IS NULL
                GROUP BY pocy_no
             ) pocy
            ON pocy.pocy_no = memb.pocy_no
     LEFT JOIN mr_member_plan_restriction mers
            ON memb.mepl_oid = mers.mepl_oid
           AND CURRENT_DATE BETWEEN mers.rstr_eff_date AND NVL(mers.rstr_term_date, TO_DATE('2100-01-01', 'YYYY-MM-DD'))
GROUP BY
    memb.mepl_oid,
    memb.mbr_no,
    memb.mbr_name,
    memb.dob,
    memb.gender,
    memb.address,
    memb.eff_date,
    memb.exp_date,
    memb.term_date,
    memb.reinst_date,
    memb.popl_oid,
    memb.plan_oid,
    memb.pocy_no,
    memb.pocy_ref_no,
    memb.payment_mode,
    memb.policy_status,
    memb.product,
    memb.plan_desc,
    memb.has_wait_period,
    memb.has_spec_dis_period,
    mefd.min_eff_date,
    mefp.insured_periods,
    pocy.min_eff_date,
    mers.rstr_desc,
    mers.rstr_desc_vn
