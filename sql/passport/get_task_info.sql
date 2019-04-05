-- passport/get_task_info.sql
SELECT 0 task_id, tm FROM PIKALKA.pass_jrn WHERE guid = :guid
UNION SELECT task_id, tm FROM PIKALKA.pass_task WHERE guid = :guid
ORDER BY task_id