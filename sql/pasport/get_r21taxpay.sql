-- pasport/get_r21taxpay.sql
SELECT t.*
FROM RG02.r21taxpay t
WHERE t.tin = :tin AND t.c_stan NOT IN (17,27)
ORDER BY t.c_stan