-- passport/rro.sql
SELECT n_fis, d_reg, '' blank
FROM RRO.rro
WHERE d_sks IS NULL AND LPAD(tin,10,'0') = LPAD(:tin,10,'0')
ORDER BY d_reg