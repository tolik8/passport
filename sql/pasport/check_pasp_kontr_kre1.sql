-- pasport/check_pasp_kontr_kre1.sql
SELECT COUNT(*)
FROM PIKALKA.pasp_kontr_kre1
WHERE guid = :guid