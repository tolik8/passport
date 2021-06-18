-- passport/access_tasks.sql
SELECT t.*, TO_CHAR(r.dt0, 'dd.mm.yyyy hh24:mi:ss') AS dt0
FROM pass_task t,
     (SELECT * FROM pass_access WHERE guid = :user_guid) a,
     (SELECT task_id, MAX(dt0) AS dt0
      FROM pass_tasks
      WHERE tin = :tin AND dt1 = :dt1 AND dt2 = :dt2
      GROUP BY tin, dt1, dt2, task_id) r
WHERE t.id = a.task_id AND t.id = r.task_id(+)
ORDER BY t.id