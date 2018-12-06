-- pasport/get_pasp_balance.sql
SELECT t.*
FROM PIKALKA.pasp_balance t
WHERE t.guid = :guid
ORDER BY t.period_year, t.period_month