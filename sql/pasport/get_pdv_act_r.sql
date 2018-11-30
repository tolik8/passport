-- pasport/get_pdv_act_r.sql
SELECT p.*
FROM AISR.pdv_act_r p
WHERE p.tin = :tin AND p.dat_anul IS NULL