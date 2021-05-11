WITH members AS
(
    SELECT
        memb.memb_oid,
        memb.mbr_no,
        NVL(memb.memb_ref_no, memb.mbr_no) memb_ref_no,
        memb.mbr_first_name,
        memb.mbr_mid_name,
        memb.mbr_last_name,
        memb.dob,
        LOWER(memb.email) email,
        COALESCE(memb.mobile_no, memb.office_no) tel,
        NVL(memb.corr_addr_1, '') || DECODE(memb.corr_addr_2, '', '', ', ' || memb.corr_addr_2) address,
        FN_GET_SYS_CODE(memb.scma_oid_sex) gender,
        FN_GET_SYS_CODE_DESC(memb.scma_oid_yn_wait_period, 'en') has_wait_period,
        NVL(FN_GET_SYS_CODE_DESC(memb.scma_oid_yn_spec_dis_period, 'en'), 'Yes') has_spec_dis_period,
        FN_GET_SYS_CODE(memb.scma_oid_mbr_type) mbr_type,
        memb.term_date,
        mepl.mepl_oid,
        mepl.reinst_date,
        popl.popl_oid,
        plan.plan_oid,
        plan.plan_id,
        plan.rev_no,
        pocy.pocy_no,
        NVL(pocy.pocy_ref_no, pocy.pocy_no) pocy_ref_no,
        CASE WHEN memb.email = poho.email THEN 1 ELSE 0 END AS is_policy_holder,
        CASE WHEN pocy.eff_date < mepl.eff_date THEN mepl.eff_date ELSE pocy.eff_date END eff_date,
        CASE WHEN pocy.exp_date < mepl.exp_date AND pocy.scma_oid_payment_mode != 'PAYMENT_MODE_S' THEN mepl.exp_date ELSE pocy.exp_date END exp_date,
        FN_GET_SYS_CODE_DESC(pocy.scma_oid_payment_mode, 'en') payment_mode,
        FN_GET_SYS_CODE_DESC(pocy.scma_oid_pocy_status, 'en') policy_status,
        FN_GET_SYS_CODE(prty.scma_oid_product) product,
        DECODE(prty.scma_oid_product, 'PRODUCT_TV_BV', plan.plan_desc, popl.pocy_plan_desc) AS plan_desc,
        pdex.plan_excls,
        pdex.plan_excls_vi,
        NVL(MAX(CASE WHEN behd.scma_oid_ben_type = 'BENEFIT_TYPE_IP' THEN DECODE(pobe.ben_type_ind, NULL, 'No', 'N', 'No', 'Yes') END), 'No') ip_ind,
        NVL(MAX(CASE WHEN behd.scma_oid_ben_type = 'BENEFIT_TYPE_OP' THEN DECODE(pobe.ben_type_ind, NULL, 'No', 'N', 'No', 'Yes') END), 'No') op_ind,
        NVL(MAX(CASE WHEN behd.scma_oid_ben_type = 'BENEFIT_TYPE_DT' THEN DECODE(pobe.ben_type_ind, NULL, 'No', 'N', 'No', 'Yes') END), 'No') dt_ind,
        MAX(CASE WHEN behd.scma_oid_ben_type = 'BENEFIT_TYPE_IP' THEN pobe.pobe_oid END) ip_oid,
        MAX(CASE WHEN behd.scma_oid_ben_type = 'BENEFIT_TYPE_OP' THEN pobe.pobe_oid END) op_oid,
        MAX(CASE WHEN behd.scma_oid_ben_type = 'BENEFIT_TYPE_DT' THEN pobe.pobe_oid END) dt_oid
    FROM mr_member memb
        INNER JOIN mr_member_plan mepl
                ON memb.memb_oid = mepl.memb_oid
        INNER JOIN mr_policy_plan popl
                ON mepl.popl_oid = popl.popl_oid
        INNER JOIN mr_policy_plan_benefit pobe
                ON pobe.popl_oid = popl.popl_oid
        INNER JOIN pd_plan_benefit plbe
                ON pobe.plbe_oid = plbe.plbe_oid
        INNER JOIN pd_ben_head behd
                ON plbe.behd_oid = behd.behd_oid
               AND behd.ben_head IS NULL
        INNER JOIN pd_plan_limit plli
                ON plbe.plli_oid = plli.plli_oid
               AND plli.plan_oid = popl.plan_oid
               AND plli.limit_type = 'T'
        INNER JOIN mr_policy pocy
                ON popl.pocy_oid = pocy.pocy_oid
        INNER JOIN pd_plan plan
                ON popl.plan_oid = plan.plan_oid
         LEFT JOIN (
            SELECT
                pdex.plan_oid,
                INITCAP(LISTAGG(diag.diag_desc, ', ') WITHIN GROUP (ORDER BY diag.diag_desc)) plan_excls,
                INITCAP(UNISTR(LISTAGG(ASCIISTR(diag.diag_desc_vn), ', ') WITHIN GROUP (ORDER BY diag.diag_oid))) plan_excls_vi
            FROM pd_plan_exclusion pdex
                JOIN rt_diagnosis diag
                  ON pdex.diag_oid_excl = diag.diag_oid
            GROUP BY
                pdex.plan_oid
         ) pdex
                ON plan.plan_oid = pdex.plan_oid
        INNER JOIN rt_product_type prty
                ON plan.prty_oid = prty.prty_oid
        LEFT JOIN mr_policyholder poho
                ON memb.poho_oid = poho.poho_oid
    WHERE prty.scma_oid_product IN ('PRODUCT_MD', 'PRODUCT_HF')
      AND pocy.proforma_ind = 'N'
      AND mepl.status IS NULL
      AND (
            ADD_MONTHS(CURRENT_DATE, -12) BETWEEN mepl.eff_date AND 1 + NVL(mepl.term_date, mepl.exp_date) OR
            (
                CURRENT_DATE BETWEEN mepl.eff_date AND 1 + NVL(mepl.term_date, mepl.exp_date) AND
                CURRENT_DATE BETWEEN pocy.eff_date AND 1 + NVL(pocy.term_date, pocy.exp_date) AND
                CURRENT_DATE BETWEEN memb.eff_date AND 1 + NVL(memb.term_date, mepl.exp_date)
            )
        )
    GROUP BY
        pocy.pocy_no,
        pocy.pocy_ref_no,
        pocy.eff_date,
        pocy.exp_date,
        pocy.scma_oid_payment_mode,
        pocy.scma_oid_pocy_status,
        poho.email,
        popl.pocy_plan_desc,
        memb.memb_oid,
        memb.mbr_no,
        memb.memb_ref_no,
        memb.mbr_last_name,
        memb.mbr_mid_name,
        memb.mbr_first_name,
        memb.dob,
        memb.email,
        memb.mobile_no,
        memb.office_no,
        memb.corr_addr_1,
        memb.corr_addr_2,
        memb.corr_addr_3,
        memb.corr_addr_4,
        memb.scma_oid_sex,
        memb.scma_oid_yn_wait_period,
        memb.scma_oid_yn_spec_dis_period,
        memb.scma_oid_mbr_type,
        memb.term_date,
        popl.popl_oid,
        mepl.mepl_oid,
        mepl.eff_date,
        mepl.exp_date,
        mepl.reinst_date,
        prty.scma_oid_product,
        plan.plan_oid,
        plan.plan_id,
        plan.rev_no,
        plan.plan_desc,
        pdex.plan_excls,
        pdex.plan_excls_vi
),
member_relation AS
(
    SELECT
        mbr_no,
        mbr_type,
        UNISTR(LISTAGG(child_no || ' - ' || ASCIISTR(child_last_name || ' ' || child_first_name) || ' - ' || child_age, ';') WITHIN GROUP (ORDER BY child_no)) children
    FROM (
        SELECT DISTINCT
            memb.mbr_no,
            memb.mbr_type,
            child.mbr_no child_no,
            FLOOR(MONTHS_BETWEEN(CURRENT_DATE, child.dob) / 12) child_age,
            child.mbr_last_name child_last_name,
            child.mbr_mid_name child_mid_name,
            child.mbr_first_name child_first_name
        FROM members memb
            JOIN members child
              ON SUBSTR(child.mbr_no, 0, LENGTH(child.mbr_no) - 2) = SUBSTR(memb.mbr_no, 0, LENGTH(memb.mbr_no) - 2)
             AND child.mbr_type = 'C'
             AND memb.mbr_type IN ('A', 'S')
        AND FLOOR(MONTHS_BETWEEN(CURRENT_DATE, child.dob) / 12) < 23
    )
    GROUP BY
        mbr_no,
        mbr_type
),
benefit_limits AS
(
    SELECT
        plli.plan_oid,
        plli.plli_oid,
        plli.limit_type AS plan_limit_type,
        plli.amt_yr AS plan_limit_amt_yr,
        plli.amt_vis AS plan_limit_amt_vis,
        plli.amt_dis_life AS plan_limit_amt_dis_life,
        plli.copay_pct,
        FN_GET_SYS_CODE(behd.scma_oid_ben_type) AS benefit_type,
        behd.ben_head AS benefit_head_code
    FROM pd_plan_limit plli
        JOIN pd_plan_benefit plbe
          ON plbe.plli_oid = plli.plli_oid
        JOIN pd_ben_head behd
          ON behd.behd_oid = plbe.behd_oid
    WHERE plli.limit_type IN ('CH', 'H', 'T')
      AND behd.scma_oid_ben_type IN ('BENEFIT_TYPE_IP', 'BENEFIT_TYPE_OP', 'BENEFIT_TYPE_DT')
      AND plli.plan_oid IN (SELECT DISTINCT plan_oid FROM members)
),
debit_notes AS
(
    SELECT
        prep.mepl_oid,
        prep.bill_fm,
        prep.bill_to,
        FN_GET_SYS_CODE(note.scma_oid_note_type) AS note_type
    FROM bf_debit_note note
        JOIN bf_premium_member_plan prep
          ON note.note_oid = prep.note_oid
        JOIN bf_premium_member_plan_benefit prem
          ON prem.prep_oid = prep.prep_oid
        JOIN mr_member_plan_benefit mebe
          ON prem.mebe_oid = mebe.mebe_oid
        JOIN members memb
          ON memb.op_oid = mebe.pobe_oid
         AND memb.mepl_oid = prep.mepl_oid
    WHERE note.reverse_date IS NULL
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
            WHERE mepl.status IS NULL
              AND mepl.memb_oid IN (SELECT DISTINCT memb_oid FROM members)
              AND pocy.proforma_ind = 'N'
        )
    )
)
SELECT DISTINCT
    ? "company",
    memb.pocy_no "pocy_no",
    memb.pocy_ref_no "pocy_ref_no",
    memb.mbr_no "mbr_no",
    memb.memb_ref_no "memb_ref_no",
    TRIM(memb.mbr_last_name) "mbr_last_name",
    TRIM(memb.mbr_mid_name) "mbr_mid_name",
    TRIM(memb.mbr_first_name) "mbr_first_name",
    TO_CHAR(memb.dob, 'YYYY-MM-DD') "dob",
    memb.gender "gender",
    memb.email "email",
    memb.tel "tel",
    memb.address "address",
    memb.payment_mode "payment_mode",
    memb.mepl_oid "mepl_oid",
    TO_CHAR(memb.eff_date, 'YYYY-MM-DD') "memb_eff_date",
    TO_CHAR(memb.exp_date, 'YYYY-MM-DD') "memb_exp_date",
    TO_CHAR(memb.term_date, 'YYYY-MM-DD') "term_date",
    TO_CHAR(memb.reinst_date, 'YYYY-MM-DD') "reinst_date",
    TO_CHAR(pocy.min_eff_date, 'YYYY-MM-DD') "min_pocy_eff_date",
    TO_CHAR(mefd.min_eff_date, 'YYYY-MM-DD') "min_memb_eff_date",
    mefp.insured_periods "insured_periods",
    memb.has_wait_period "wait_period",
    memb.has_spec_dis_period "spec_dis_period",
    memb.product "product",
    memb.plan_desc "plan_desc",
    memb.plan_excls "plan_excls",
    memb.plan_excls_vi "plan_excls_vi",
    DECODE(MAX(beli_ip.plan_limit_amt_dis_life), NULL, NULL, TRIM(mers.rstr_desc)) "memb_rstr",
    DECODE(MAX(beli_ip.plan_limit_amt_dis_life), NULL, NULL, TRIM(mers.rstr_desc_vn)) "memb_rstr_vi",
    mein.mepl_incls "mepl_incls",
    mein.mepl_incls_vi "mepl_incls_vi",
    meex.mepl_excls "mepl_excls",
    meex.mepl_excls_vi "mepl_excls_vi",
    memb.policy_status "policy_status",
    DECODE(mefd.min_eff_date, memb.eff_date, 'No', 'Yes') "is_renew",
    memb.op_ind "op_ind",
    memb.dt_ind "dt_ind",
    memb.plan_id,
    memb.rev_no,
    CASE
        WHEN EXISTS(
            SELECT 1 FROM debit_notes
            WHERE mepl_oid = memb.mepl_oid
              AND (
                CURRENT_DATE BETWEEN bill_fm AND bill_to OR
                memb.exp_date <= bill_to
              )
        )
            THEN 'Yes'
        ELSE 'No'
    END "has_debit_note",
	REPLACE(merl.children, CHR(0)) "children",
    memb.is_policy_holder "is_policy_holder"
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
            RTRIM(LISTAGG(period_part) WITHIN GROUP (ORDER BY row_id), ', ') insured_periods
        FROM (
            SELECT
                ROWNUM AS row_id,
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
     LEFT JOIN benefit_limits beli_ip
            ON beli_ip.plan_oid = memb.plan_oid
           AND beli_ip.benefit_type = 'IP'
           AND plan_limit_amt_dis_life IS NOT NULL
     LEFT JOIN mr_member_plan_restriction mers
            ON memb.mepl_oid = mers.mepl_oid
           AND CURRENT_DATE BETWEEN mers.rstr_eff_date AND NVL(mers.rstr_term_date, TO_DATE('2100-01-01', 'YYYY-MM-DD'))
     LEFT JOIN (
        SELECT
            mein.mepl_oid,
            INITCAP(LISTAGG(diag.diag_desc, ', ') WITHIN GROUP (ORDER BY diag.diag_desc)) mepl_incls,
            INITCAP(UNISTR(LISTAGG(ASCIISTR(diag.diag_desc_vn), ', ') WITHIN GROUP (ORDER BY diag.diag_oid))) mepl_incls_vi
        FROM mr_member_plan_inclusion mein
            JOIN rt_diagnosis diag
              ON mein.diag_oid_incl = diag.diag_oid
        GROUP BY
            mein.mepl_oid
     ) mein
            ON memb.mepl_oid = mein.mepl_oid
     LEFT JOIN (
        SELECT
            meex.mepl_oid,
            INITCAP(LISTAGG(diag.diag_desc, ', ') WITHIN GROUP (ORDER BY diag.diag_desc)) mepl_excls,
            INITCAP(UNISTR(LISTAGG(ASCIISTR(diag.diag_desc_vn), ', ') WITHIN GROUP (ORDER BY diag.diag_oid))) mepl_excls_vi
        FROM mr_member_plan_exclusion meex
            JOIN rt_diagnosis diag
              ON meex.diag_oid_excl = diag.diag_oid
        GROUP BY
            meex.mepl_oid
     ) meex
            ON memb.mepl_oid = meex.mepl_oid
     LEFT JOIN member_relation merl
			ON memb.mbr_no = merl.mbr_no
GROUP BY
    memb.mepl_oid,
    memb.mbr_no,
    memb.memb_ref_no,
    memb.mbr_first_name,
    memb.mbr_mid_name,
    memb.mbr_last_name,
    memb.dob,
    memb.gender,
    memb.email,
    memb.tel,
    memb.address,
    memb.eff_date,
    memb.exp_date,
    memb.term_date,
    memb.reinst_date,
    memb.popl_oid,
    memb.plan_oid,
    memb.plan_id,
    memb.rev_no,
    memb.pocy_no,
    memb.pocy_ref_no,
    memb.payment_mode,
    memb.policy_status,
    memb.product,
    memb.plan_desc,
    memb.plan_excls,
    memb.plan_excls_vi,
    memb.has_wait_period,
    memb.has_spec_dis_period,
    mefd.min_eff_date,
    mefp.insured_periods,
    pocy.min_eff_date,
    memb.op_ind,
    memb.dt_ind,
    beli_ip.plan_limit_amt_dis_life,
    mers.rstr_desc,
    mers.rstr_desc_vn,
    mein.mepl_incls,
    mein.mepl_incls_vi,
    meex.mepl_excls,
    meex.mepl_excls_vi,
    merl.children,
    memb.is_policy_holder
