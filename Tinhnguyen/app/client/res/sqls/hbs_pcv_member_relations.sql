SELECT
    memb.mbr_no "mbr_no",
    INITCAP(memb.mbr_last_name || ' ' || memb.mbr_first_name) "mbr_name"
FROM (
    SELECT
        mbr_no,
        FN_GET_SYS_CODE(scma_oid_mbr_type) mbr_type
    FROM mr_member
    WHERE TRIM(LEADING 0 FROM mbr_no) = TRIM(LEADING 0 FROM ?)
) self
    CROSS JOIN (
        SELECT
            mbr_no,
            mbr_last_name,
            mbr_first_name,
            FN_GET_SYS_CODE(scma_oid_mbr_type) mbr_type
        FROM mr_member
        WHERE TRIM(LEADING 0 FROM SUBSTR(mbr_no, 0, LENGTH(mbr_no) - 1)) = TRIM(LEADING 0 FROM SUBSTR(?, 0, LENGTH(?) - 1))
        AND FLOOR(MONTHS_BETWEEN(CURRENT_DATE, dob) / 12) < ?
    ) memb
WHERE (self.mbr_type = 'A' AND memb.mbr_type IN ('A', 'C'))
   OR (self.mbr_type = 'S' AND memb.mbr_type IN ('S', 'C'))
   OR (self.mbr_type NOT IN ('A', 'S') AND self.mbr_no = memb.mbr_no)