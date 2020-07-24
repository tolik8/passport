-- get_lic
SELECT t.*, '' AS blank
FROM PIKALKA.lic t
WHERE tin = '24627614'
ORDER BY d_begin, n
