-- passport/access_tasks.sql
SELECT t.*, to_char(r.dt0, 'dd.mm.yyyy hh24:mi:ss') AS dt0
FROM PIKALKA.d_pass_task t,
     (SELECT * FROM PIKALKA.pass_access WHERE guid = :user_guid) a,
     (SELECT task_id, MAX(dt0) dt0
      FROM PIKALKA.pass_task
      WHERE tin = :tin AND dt1 = :dt1 AND dt2 = :dt2
      GROUP BY tin, dt1, dt2, task_id) r
WHERE t.id = a.task_id AND t.id = r.task_id(+)
ORDER BY t.id