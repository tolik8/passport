-- pasport/check_pasp_kontr_kre2.sql
SELECT COUNT(*)
FROM PIKALKA.pasp_kontr_kre2
WHERE guid = :guid