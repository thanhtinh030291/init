SELECT
    bug.id,
    bug.summary,
    bug_text.description,
    call_time.value call_time,
    tel_no.value tel_no
FROM mantis_bug_table bug
    INNER JOIN mantis_bug_text_table bug_text
            ON bug_text.id = bug.bug_text_id
    INNER JOIN mantis_custom_field_string_table mbr_no
            ON mbr_no.field_id = 1
           AND mbr_no.bug_id = bug.id
     LEFT JOIN mantis_custom_field_string_table call_time
            ON call_time.field_id = 21
           AND call_time.bug_id = bug.id
     LEFT JOIN mantis_custom_field_string_table tel_no
            ON tel_no.field_id = 22
           AND tel_no.bug_id = bug.id
WHERE bug.project_id = 1
  AND TRIM(LEADING '0' FROM mbr_no.value) = ?
ORDER BY bug.date_submitted DESC
LIMIT 25