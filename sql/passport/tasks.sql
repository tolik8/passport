-- passport/tasks.sql
SELECT t.*, to_char(r.dt0, 'dd.mm.yyyy hh24:mi:ss') AS dt0
FROM pass_task t,
     (SELECT task_id, MAX(dt0) dt0
      FROM pass_tasks
      WHERE tin = :tin AND dt1 = :dt1 AND dt2 = :dt2
      GROUP BY tin, dt1, dt2, task_id) r
WHERE t.id = r.task_id(+)
ORDER BY t.id