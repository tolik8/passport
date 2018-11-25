-- pasport/check.sql
SELECT TO_CHAR(l.dt0, 'dd.mm.yyyy hh24:mi:ss') dt0, u.fio1, u.fio2, u.fio3, l.guid
FROM PIKALKA.people u,
    (SELECT * FROM PIKALKA.pasp_log
    WHERE dt1 = :dt1 AND dt2 = :dt2 AND tin = :tin /*AND dt0 IN
        (SELECT MAX(dt0) FROM PIKALKA.pasp_log
        WHERE dt1 = :dt1 AND dt2 = :dt2 AND tin = :tin
        GROUP BY dt1, dt2, tin)*/) l
WHERE l.guid_user = u.guid(+)
ORDER BY l.dt0 DESC