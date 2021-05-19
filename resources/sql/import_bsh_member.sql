WITH members AS
(
  SELECT
    MAX(memb.memb_oid) AS memb_oid,
    MAX(memb.mbr_no) AS mbr_no,
    MAX(NVL(memb.memb_ref_no, memb.mbr_no)) AS memb_ref_no,
    TRIM(MAX(memb.mbr_first_name)) AS mbr_first_name,
    MAX(memb.dob) AS dob,
    MAX(LOWER(memb.email)) AS email,
    MAX(COALESCE(memb.mobile_no, memb.office_no)) AS tel,
    MAX(NVL(memb.corr_addr_1, '') || DECODE(memb.corr_addr_2, '', '', ', ' || memb.corr_addr_2)) AS address,
    FN_GET_SYS_CODE(MAX(memb.scma_oid_sex)) AS gender,
    FN_GET_SYS_CODE(MAX(memb.scma_oid_mbr_type)) AS mbr_type,
    MAX(memb.term_date) AS term_date,
    mepl.mepl_oid,
    MAX(mepl.reinst_date) AS reinst_date,
    MAX(plan.plan_oid) AS plan_oid,
    MAX(pocy.pocy_no) AS pocy_no,
    MAX(NVL(pocy.pocy_ref_no, pocy.pocy_no)) AS pocy_ref_no,
    CASE WHEN MAX(memb.email) = MAX(poho.email) THEN 1 ELSE 0 END AS is_policy_holder,
    MAX(mepl.eff_date) AS eff_date,
    MAX(mepl.exp_date) AS exp_date,
    FN_GET_SYS_CODE(MAX(prty.scma_oid_product)) AS product,
    FN_GET_SYS_CODE(MAX(prty.scma_oid_prod_type)) AS prod_type,
    MAX(popl.pocy_plan_desc) AS plan_desc,
    FN_GET_SYS_CODE(MAX(memb.mbr_vip)) AS is_vip,
    MAX(memb.mbr_mobileapp) AS mobile_level
  FROM mr_member memb
  JOIN mr_policyholder poho ON memb.poho_oid = poho.poho_oid
  JOIN mr_member_plan mepl ON memb.memb_oid = mepl.memb_oid
  JOIN mr_policy_plan popl ON mepl.popl_oid = popl.popl_oid
  JOIN mr_policy pocy ON popl.pocy_oid = pocy.pocy_oid
  JOIN pd_plan plan ON popl.plan_oid = plan.plan_oid
  JOIN rt_product_type prty ON plan.prty_oid = prty.prty_oid
  WHERE mepl.status IS NULL
    AND memb.mbr_mobileapp IS NOT NULL
    AND (
      ADD_MONTHS(CURRENT_DATE, -12) BETWEEN mepl.eff_date AND NVL(mepl.term_date, mepl.exp_date)
      OR (
        CURRENT_DATE BETWEEN mepl.eff_date AND NVL(mepl.term_date, mepl.exp_date)
        AND CURRENT_DATE BETWEEN pocy.eff_date AND NVL(pocy.term_date, pocy.exp_date)
        AND CURRENT_DATE BETWEEN memb.eff_date AND NVL(memb.term_date, mepl.exp_date)
      )
    )
  GROUP BY mepl.mepl_oid
),
member_relation AS
(
  SELECT
    mbr_no,
    mbr_type,
    UNISTR(LISTAGG(child_no || ' - ' || ASCIISTR(child_first_name) || ' - ' || child_age, ';') WITHIN GROUP (ORDER BY child_no)) children
  FROM (
    SELECT DISTINCT
      memb.mbr_no,
      memb.mbr_type,
      child.mbr_no child_no,
      FLOOR(MONTHS_BETWEEN(CURRENT_DATE, child.dob) / 12) child_age,
      child.mbr_first_name child_first_name
    FROM members memb
    JOIN members child ON SUBSTR(child.mbr_no, 0, LENGTH(child.mbr_no) - 2) = SUBSTR(memb.mbr_no, 0, LENGTH(memb.mbr_no) - 2)
    WHERE child.mbr_type = 'C'
      AND memb.mbr_type IN ('A', 'S')
      AND FLOOR(MONTHS_BETWEEN(CURRENT_DATE, child.dob) / 12) < 23
  )
  GROUP BY mbr_no, mbr_type
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
        memb_oid,
        eff_date,
        NVL(term_date, exp_date) exp_date
      FROM mr_member_plan
      WHERE status IS NULL
      AND memb_oid IN (SELECT DISTINCT memb_oid FROM members)
    )
  )
)
SELECT DISTINCT
  ? "company",
  memb.pocy_no,
  memb.pocy_ref_no,
  memb.mbr_no,
  memb.memb_ref_no,
  memb.mbr_first_name,
  TO_CHAR(memb.dob, 'YYYY-MM-DD') "dob",
  memb.gender,
  memb.email,
  memb.tel,
  memb.address,
  memb.mepl_oid,
  TO_CHAR(memb.eff_date, 'YYYY-MM-DD') "memb_eff_date",
  TO_CHAR(memb.exp_date, 'YYYY-MM-DD') "memb_exp_date",
  TO_CHAR(memb.term_date, 'YYYY-MM-DD') "term_date",
  TO_CHAR(memb.reinst_date, 'YYYY-MM-DD') "reinst_date",
  TO_CHAR(pocy.min_eff_date, 'YYYY-MM-DD') "min_pocy_eff_date",
  TO_CHAR(mefd.min_eff_date, 'YYYY-MM-DD') "min_memb_eff_date",
  mefp.insured_periods,
  memb.product,
  memb.plan_desc,
  TRIM(MAX(mers.rstr_desc)) AS "memb_rstr",
  mein.mepl_incls,
  mein.mepl_incls_vi,
  meex.mepl_excls,
  meex.mepl_excls_vi,
  DECODE(mefd.min_eff_date, memb.eff_date, 'No', 'Yes') "is_renew",
  memb.plan_oid,
	REPLACE(merl.children, CHR(0)) "children",
  memb.is_policy_holder,
  memb.prod_type,
  memb.is_vip,
  memb.mobile_level
FROM members memb
JOIN (
  SELECT
    pocy_no,
    MIN(eff_date) AS min_eff_date
  FROM mr_policy
  WHERE status IS NULL
  GROUP BY pocy_no
) pocy ON pocy.pocy_no = memb.pocy_no
JOIN (
  SELECT
    memb_oid,
    MAX(eff_date) KEEP (DENSE_RANK LAST ORDER BY first_year NULLS FIRST) min_eff_date
    FROM member_eff_dates
    GROUP BY memb_oid
) mefd ON mefd.memb_oid = memb.memb_oid
JOIN (
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
) mefp ON mefp.memb_oid = memb.memb_oid
LEFT JOIN mr_member_plan_restriction mers ON memb.mepl_oid = mers.mepl_oid
  AND CURRENT_DATE BETWEEN mers.rstr_eff_date AND NVL(mers.rstr_term_date, TO_DATE('2100-01-01', 'YYYY-MM-DD'))
LEFT JOIN (
  SELECT
    mein.mepl_oid,
    INITCAP(LISTAGG(diag.diag_desc, ', ') WITHIN GROUP (ORDER BY diag.diag_desc)) mepl_incls,
    INITCAP(UNISTR(LISTAGG(ASCIISTR(diag.diag_desc_vn), ', ') WITHIN GROUP (ORDER BY diag.diag_oid))) mepl_incls_vi
  FROM mr_member_plan_inclusion mein
  JOIN rt_diagnosis diag ON mein.diag_oid_incl = diag.diag_oid
  GROUP BY mein.mepl_oid
) mein ON memb.mepl_oid = mein.mepl_oid
LEFT JOIN (
  SELECT
    meex.mepl_oid,
    INITCAP(LISTAGG(diag.diag_desc, ', ') WITHIN GROUP (ORDER BY diag.diag_desc)) mepl_excls,
    INITCAP(UNISTR(LISTAGG(ASCIISTR(diag.diag_desc_vn), ', ') WITHIN GROUP (ORDER BY diag.diag_oid))) mepl_excls_vi
  FROM mr_member_plan_exclusion meex
  JOIN rt_diagnosis diag ON meex.diag_oid_excl = diag.diag_oid
  GROUP BY meex.mepl_oid
) meex ON memb.mepl_oid = meex.mepl_oid
LEFT JOIN member_relation merl ON memb.mbr_no = merl.mbr_no
GROUP BY
  memb.pocy_no,
  memb.pocy_ref_no,
  memb.mbr_no,
  memb.memb_ref_no,
  memb.mbr_first_name,
  memb.dob,
  memb.gender,
  memb.email,
  memb.tel,
  memb.address,
  memb.mepl_oid,
  memb.eff_date,
  memb.exp_date,
  memb.term_date,
  memb.reinst_date,
  pocy.min_eff_date,
  mefd.min_eff_date,
  mefp.insured_periods,
  memb.product,
  memb.plan_desc,
  mein.mepl_incls,
  mein.mepl_incls_vi,
  meex.mepl_excls,
  meex.mepl_excls_vi,
  mefd.min_eff_date,
  memb.plan_oid,
	merl.children,
  memb.is_policy_holder,
  memb.prod_type,
  memb.is_vip,
  memb.mobile_level
