SELECT DISTINCT
    LISTAGG(regexp_substr(id, '[^,]+$'), ';') WITHIN GROUP(ORDER BY id) OVER (PARTITION BY mbr_no) AS mempl_oids,
    LISTAGG(pocy_plan_desc, '; ') WITHIN GROUP(ORDER BY id) OVER (PARTITION BY mbr_no) AS plan_descs,
    mbr_name,
    memb.mbr_last_name || ' ' || memb.mbr_first_name AS mbr_name_vi,
    mbr_no,
    pocy_no,
    SUBSTR(pocy_no, 0, 6) || '-' || SUBSTR(pocy_no, 7, 3) || '-' || SUBSTR(pocy_no, 10) AS pocy_no_s,
    SUBSTR(mbr_no, 0, 7) || '-' || SUBSTR(mbr_no, 8, 2) as mbr_no_s,
    TO_CHAR(vwmi.eff_date, 'DD-MON-YYYY') as eff_date,
    TO_CHAR(vwmi.exp_date, 'MM-MON-YYYY') as exp_date,
    TO_CHAR(vwmi.dob, 'DD/MM/YYYY') as dob,
    medical_waiting_period
FROM vw_rp_member_info vwmi 
	join mr_member memb using (mbr_no)
where mbr_no = ?
-- GROUP BY mbr_no