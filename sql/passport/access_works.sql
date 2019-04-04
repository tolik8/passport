-- passport/access_works.sql
SELECT i.*, to_char(r.dt0, 'dd.mm.yyyy hh24:mi:ss') dt0
FROM PIKALKA.d_pass_info i,
     (SELECT * FROM PIKALKA.pass_access WHERE guid = :user_guid) a,
     (SELECT work_id, MAX(dt0) dt0
      FROM PIKALKA.pass_work
      WHERE tin = :tin AND dt1 = :dt1 AND dt2 = :dt2
      GROUP BY tin, dt1, dt2, work_id) r
WHERE i.id = a.work_id AND i.id = r.work_id(+)
ORDER BY i.id