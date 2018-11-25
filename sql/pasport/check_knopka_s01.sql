-- pasport/check_knopka_s01.sql
SELECT COUNT(*)
FROM PIKALKA.knopka_s01
WHERE cp_tin = :tin
    AND dt0 = TO_DATE(SYSDATE)
    AND dt1 = :dt1
    AND dt2 = :dt2