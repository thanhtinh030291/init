SELECT
    pocy.pocy_no `pocy_no`,
    memb.mbr_no `mbr_no`,
    memb.corr_addr_1 || ', ' || memb.corr_addr_2 || ', ' || memb.corr_addr_3 || ', ' || memb.corr_addr_4 `address`,
    LOWER(memb.email) `email`,
    INITCAP(memb.mbr_last_name || ' ' || memb.mbr_first_name) `fullname`,
    COALESCE(memb.mobile_no, memb.office_no) `tel`
FROM mr_member memb
    JOIN mr_member_plan mepl
        ON mepl.memb_oid = memb.memb_oid
    JOIN mr_policy_plan popl
        ON mepl.popl_oid = popl.popl_oid
    JOIN mr_policy pocy
        ON popl.pocy_oid = pocy.pocy_oid
WHERE memb.mbr_no = ?
  AND pocy.proforma_ind = 'N'
--   AND NVL(memb.email || ' ', ' ') != ' '
  AND memb.term_date IS NULL
  AND FLOOR(MONTHS_BETWEEN(CURRENT_DATE, memb.dob) / 12) >= ?