-- pasport/check_pasp_kontr_zob1.sql
SELECT COUNT(*)
FROM PIKALKA.pasp_kontr_zob1
WHERE guid = :guid