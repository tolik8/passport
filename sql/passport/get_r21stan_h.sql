-- passport/get_r21stan_h.sql
SELECT ROWNUM n, x.* FROM (
SELECT old_stan, new_stan, d_change, is_actual
FROM RG02.r21stan_h 
WHERE c_distr = :c_distr AND guid = 
    (SELECT GUID FROM RG02.r21taxpay WHERE tin = :tin AND c_distr = :c_distr AND ROWNUM = 1)
ORDER BY d_change) x