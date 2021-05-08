SELECT
    mbr_no "mbr_no",
    dob "dob",
    MAX(memb_eff_date) "memb_eff_date",
    MAX(memb_exp_date) "memb_exp_date",
    MAX(term_date) "term_date",
    cl_no "cl_no",
    clam_oid "clam_oid",
    clli_oid "clli_oid",
    incur_date_from "incur_date_from",
    incur_date_to "incur_date_to",
    ben_type "ben_type",
    ben_head "ben_head",
    status "status",
    INITCAP(diag_desc) "diag_desc",
    INITCAP(diag_desc_vn) "diag_desc_vi",
    prov_code "prov_code",
    INITCAP(prov_name) "prov_name",
    DECODE(pres_amt, 0, pres_amt_org, pres_amt) "pres_amt",
    DECODE(app_amt, 0, app_amt_org, app_amt) "app_amt"
FROM (
    SELECT
        memb.mbr_no,
        TO_CHAR(memb.dob, 'DD/MM/YYYY') dob,
        TO_CHAR(pocy.eff_date, 'YYYY') pocy_eff_year,
        TO_CHAR(
            CASE
                WHEN pocy.eff_date < mepl.eff_date
                    THEN mepl.eff_date
                ELSE pocy.eff_date
            END,
            'DD/MM/YYYY'
        ) memb_eff_date,
        TO_CHAR(
            CASE
                WHEN (
                    pocy.exp_date < mepl.exp_date AND
                    pocy.scma_oid_payment_mode != 'PAYMENT_MODE_S'
                ) THEN mepl.exp_date
                ELSE pocy.exp_date
            END,
            'DD/MM/YYYY'
        ) memb_exp_date,
        TO_CHAR(memb.term_date, 'DD/MM') term_date,
        clam.cl_no,
        clam.clam_oid,
        clli.clli_oid,
        TO_CHAR(clli.incur_date_from, 'DD/MM/YYYY') incur_date_from,
        TO_CHAR(clli.incur_date_to, 'DD/MM/YYYY') incur_date_to,
        FN_GET_SYS_CODE(behd.scma_oid_ben_type) ben_type,
        behd.ben_head,
        FN_GET_SYS_CODE(clli.scma_oid_cl_line_status) status,
        diag.diag_desc,
        diag.diag_desc_vn,
        prov.prov_code,
        prov.prov_name,
        CL_COMMON_BAT_PKG.CONV_TO_PLAN_CCY(
            clli.popl_oid,
            'CCY_VNO',
            clli.incur_date_from,
            clli.pres_amt
        ) pres_amt,
        clli.pres_amt pres_amt_org,
        CL_COMMON_BAT_PKG.CONV_TO_PLAN_CCY(
            clli.popl_oid,
            'CCY_VNO',
            clli.incur_date_from,
            clli.app_amt
        ) app_amt,
        clli.app_amt app_amt_org
    FROM cl_claim clam
        INNER JOIN cl_line clli
                ON clli.clam_oid = clam.clam_oid
        INNER JOIN pd_ben_head behd
                ON behd.behd_oid = clli.behd_oid
        INNER JOIN rt_diagnosis diag
                ON diag.diag_oid = clli.diag_oid
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
      AND mepl.status IS NULL
      AND prty.scma_oid_product = 'PRODUCT_MD'
      AND (
            TRIM(LEADING 0 FROM memb.memb_ref_no) = ? OR
            TRIM(LEADING 0 FROM memb.mbr_no) = ?
        )
)
GROUP BY
    mbr_no,
    dob,
    cl_no,
    clam_oid,
    clli_oid,
    incur_date_from,
    incur_date_to,
    ben_type,
    ben_head,
    status,
    diag_desc,
    diag_desc_vn,
    prov_code,
    prov_name,
    pres_amt_org,
    app_amt_org,
    pres_amt,
    app_amt
ORDER BY
    mbr_no,
    cl_no,
    clam_oid,
    clli_oid,
    incur_date_from,
    incur_date_to,
    ben_type,
    ben_head,
    diag_desc,
    prov_code,
    pres_amt
