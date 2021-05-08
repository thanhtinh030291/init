SELECT
    pdtp.area_cover,
    pdtprow.heading,
    CASE
        WHEN INSTR(LOWER(pdtprow.detail),'@copay@') > 0 then
            replace(pdtprow.detail, '@copay@', ?)
        WHEN INSTR(LOWER(pdtprow.detail),'@amtperday@') > 0 then
            replace(pdtprow.detail, '@amtperday@', ?)
        WHEN INSTR(LOWER(pdtprow.detail), '@amtpervis@') > 0 then
            replace(pdtprow.detail, '@amtpervis@', ?)
        ELSE pdtprow.detail
    END AS detail,
    CASE
        WHEN INSTR(LOWER(pdtprow.limit),'@copay@') > 0 then
            replace(pdtprow.limit, '@copay@', ?)
        WHEN INSTR(LOWER(pdtprow.limit),'@amtperday@') > 0 then
            replace(pdtprow.limit, '@amtperday@', ?)
        WHEN INSTR(LOWER(pdtprow.limit), '@amtpervis@') > 0 then
            replace(pdtprow.limit, '@amtpervis@', ?)
        ELSE pdtprow.limit
    END AS limit
FROM pd_ben_sche_template pdtp
    JOIN pd_ben_sche_template_row pdtprow USING ( pdtp_oid )
WHERE pdtp.temp_id = ?
ORDER BY pdtprow.seq