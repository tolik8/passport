-- pasport/check.sql
SELECT TO_CHAR(l.dt0, 'dd.mm.yyyy hh24:mi:ss') dt0, u.fio1, u.fio2, u.fio3, l.guid
FROM PIKALKA.people u,
    (SELECT * FROM PIKALKA.pasp_jrn_old
    WHERE dt1 = :dt1 AND dt2 = :dt2 AND tin = :tin AND dt0 >= SYSDATE - 3) l
WHERE l.guid_user = u.guid(+)
ORDER BY l.dt0 DESC