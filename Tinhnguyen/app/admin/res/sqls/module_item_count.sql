SELECT '{single}' `module`, 'Created' `action`, COUNT(*) `count`
FROM {id} A
WHERE `upd_at` IS NULL
UNION
SELECT '{single}' `module`, 'Updated' `action`, COUNT(*) `count`
FROM {id} A
WHERE `upd_at` IS NOT NULL
UNION
SELECT '{single}' `module`, 'All' `action`, COUNT(*) `count`
FROM {id}