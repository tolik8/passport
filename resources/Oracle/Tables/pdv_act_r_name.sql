CREATE TABLE AISR.pdv_act_r_name AS
SELECT * FROM AISR.pdv_act_r
WHERE dat_anul IS NULL AND c_sti_main || tin || to_date(dat_reestr) || to_date(dat_svd) IN (
SELECT c_sti_main || tin || to_date(MAX(dat_reestr)) || to_date(MAX(dat_svd))
FROM AISR.pdv_act_r
WHERE dat_anul IS NULL
GROUP BY c_sti_main, tin
