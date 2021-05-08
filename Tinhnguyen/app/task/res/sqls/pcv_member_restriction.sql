SELECT
    rstr_desc,
    rstr_desc_vn
FROM mr_member_plan_restriction mers
WHERE mers.mepl_oid = ?
  AND scma_oid_restriction_code = 'RESTRICTION_EXCL'
ORDER BY mers.mers_oid