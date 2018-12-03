-- pasport/check_pasp_kontr_zob2.sql
SELECT COUNT(*)
FROM PIKALKA.pasp_kontr_zob2
WHERE guid = :guid