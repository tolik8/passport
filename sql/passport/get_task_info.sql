-- passport/get_task_info.sql
SELECT 0 task_id, tm FROM pass_jrn WHERE guid = :guid
UNION SELECT task_id, tm FROM pass_tasks WHERE guid = :guid
ORDER BY task_id