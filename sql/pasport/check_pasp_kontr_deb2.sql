-- pasport/check_pasp_kontr_deb2.sql
SELECT COUNT(*)
FROM PIKALKA.pasp_kontr_deb2
WHERE guid = :guid