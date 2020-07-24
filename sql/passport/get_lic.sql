-- get_lic
SELECT t.*, '' AS blank
FROM PIKALKA.lic t
WHERE tin = :tin
ORDER BY d_begin, n
