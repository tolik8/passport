-- pasport/check_pasp_kontr_deb1.sql
SELECT COUNT(*)
FROM PIKALKA.pasp_kontr_deb1
WHERE guid = :guid