-- passport/selected_tasks.sql
SELECT t.*
FROM pass_task t,
    (SELECT * FROM pass_access WHERE guid = :guid) a
WHERE t.id = a.task_id AND INSTR(',' || :task || ',', ',' || t.id || ',') > 0
ORDER BY t.id