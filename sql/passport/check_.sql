-- passport/check.sql
SELECT TO_CHAR(l.dt0, 'dd.mm.yyyy hh24:mi:ss') AS dt0, u.fio1, u.fio2, u.fio3, l.guid, l.tm
FROM TOLIK.users u,
    (SELECT * FROM pass_jrn
    WHERE dt1 = :dt1 AND dt2 = :dt2 AND tin = :tin) l
WHERE l.guid_user = u.guid(+)
ORDER BY l.dt0 DESC