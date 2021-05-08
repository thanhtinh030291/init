SELECT
    'fubon' "company",
    mbr_no "mbr_no",
    dob "dob",
    MAX(memb_eff_date) "memb_eff_date",
    MAX(memb_exp_date) "memb_exp_date",
    MAX(term_date) "term_date",
    cl_no "cl_no",
    db_ref_no "db_ref_no",
    incur_date_from "incur_date_from",
    ben_head "ben_head",
    ben_type "ben_type",
    status "status",
    diag_code "diag_code",
    prov_code "prov_code",
    prov_name "prov_name",
    pres_amt "pres_amt",
    app_amt "app_amt"
FROM (
    SELECT
        memb.mbr_no,
        TO_CHAR(memb.dob, 'YYYY-MM-DD') dob,
        TO_CHAR(pocy.eff_date, 'YYYY') pocy_eff_year,
        TO_CHAR(
            CASE
                WHEN pocy.eff_date < mepl.eff_date
                    THEN mepl.eff_date
                ELSE pocy.eff_date
            END,
            'YYYY-MM-DD'
        ) memb_eff_date,
        TO_CHAR(
            CASE
                WHEN (
                    pocy.exp_date < mepl.exp_date AND
                    pocy.scma_oid_payment_mode != 'PAYMENT_MODE_S'
                ) THEN mepl.exp_date
                ELSE pocy.exp_date
            END,
            'YYYY-MM-DD'
        ) memb_exp_date,
        TO_CHAR(memb.term_date, 'YYYY-MM-DD') term_date,
        clam.cl_no,
        clli.db_ref_no,
        TO_CHAR(clli.incur_date_from, 'YYYY-MM-DD') incur_date_from,
        behd.ben_head,
        FN_GET_SYS_CODE(behd.scma_oid_ben_type) ben_type,
        FN_GET_SYS_CODE(clli.scma_oid_cl_line_status) status,
        diag.diag_code,
        prov.prov_code,
        prov.prov_name,
        CASE WHEN clli.pres_amt < 0 THEN 0 ELSE clli.pres_amt END pres_amt,
        CASE WHEN clli.app_amt < 0 THEN 0 ELSE clli.app_amt END app_amt
    FROM cl_claim clam
        INNER JOIN cl_line clli
                ON clli.clam_oid = clam.clam_oid
        INNER JOIN pd_ben_head behd
                ON behd.behd_oid = clli.behd_oid
         LEFT JOIN rt_diagnosis diag
                ON clli.diag_oid = diag.diag_oid
         LEFT JOIN pv_provider prov
                ON clli.prov_oid = prov.prov_oid
        INNER JOIN mr_member memb
                ON memb.memb_oid = clli.memb_oid
        INNER JOIN mr_member_plan mepl
                ON memb.memb_oid = mepl.memb_oid
        INNER JOIN mr_policy_plan popl
                ON mepl.popl_oid = popl.popl_oid
               AND clli.popl_oid = popl.popl_oid
        INNER JOIN mr_policy pocy
                ON popl.pocy_oid = pocy.pocy_oid
        INNER JOIN pd_plan plan
                ON popl.plan_oid = plan.plan_oid
        INNER JOIN rt_product_type prty
                ON plan.prty_oid = prty.prty_oid
    WHERE clli.rev_date IS NULL
      AND clli.scma_oid_cl_line_status != 'CL_LINE_STATUS_RV'
      AND behd.scma_oid_ben_type IN (
            'BENEFIT_TYPE_OP',
            'BENEFIT_TYPE_DT'
        )
      AND mepl.status IS NULL
      AND prty.scma_oid_product = 'PRODUCT_MD'
    --AND pocy.proforma_ind = 'N'
      AND (
            ADD_MONTHS(CURRENT_DATE, -12) BETWEEN mepl.eff_date AND NVL(mepl.term_date, mepl.exp_date) OR
            CURRENT_DATE BETWEEN mepl.eff_date AND NVL(mepl.term_date, mepl.exp_date) AND
            CURRENT_DATE BETWEEN pocy.eff_date AND NVL(pocy.term_date, pocy.exp_date) AND
            CURRENT_DATE BETWEEN memb.eff_date AND NVL(memb.term_date, mepl.exp_date)
        )
)
GROUP BY
    mbr_no,
    dob,
    cl_no,
    db_ref_no,
    incur_date_from,
    ben_head,
    ben_type,
    status,
    diag_code,
    prov_code,
    prov_name,
    pres_amt,
    app_amt
ORDER BY
    mbr_no,
    cl_no,
    incur_date_from,
    diag_code,
    prov_code,
    pres_amt
