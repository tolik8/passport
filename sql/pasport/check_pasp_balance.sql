-- pasport/check_pasp_balance.sql
SELECT COUNT(*)
FROM PIKALKA.pasp_balance
WHERE guid = :guid