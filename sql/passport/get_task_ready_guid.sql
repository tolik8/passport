-- passport/get_task_ready_guid.sql
SELECT t.task_id, t.guid
FROM PIKALKA.pass_task t,
    (SELECT * FROM PIKALKA.pass_access WHERE guid = :guid_user) a,
    
    (SELECT task_id, tin, dt1, dt2, MAX(dt0) dt0
    FROM PIKALKA.pass_task
    WHERE guid_ready IS NULL AND tin = :tin AND dt1 = :dt1 AND dt2 = :dt2 AND qq.in_comma_string(task_id, :tasks) = 1
    GROUP BY task_id, tin, dt1, dt2) x
    
WHERE t.task_id = a.task_id
    AND t.task_id = x.task_id
    AND t.tin = x.tin
    AND t.dt1 = x.dt1
    AND t.dt2 = x.dt2
    AND t.dt0 = x.dt0