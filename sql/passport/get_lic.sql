-- get_lic
SELECT t.*, '' AS blank
FROM TOLIK.pass_lic t
WHERE tin = :tin
ORDER BY d_begin, n
