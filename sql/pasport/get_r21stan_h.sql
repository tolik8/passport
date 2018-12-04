-- pasport/get_r21stan_h.sql
SELECT ROWNUM n, old_stan, new_stan, d_change, is_actual
FROM RG02.r21stan_h 
WHERE c_distr = 1918 AND guid = (SELECT GUID FROM RG02.r21taxpay WHERE tin = 300400 AND ROWNUM = 1)
ORDER BY d_change