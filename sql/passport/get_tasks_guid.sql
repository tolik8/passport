-- passport/get_tasks_guid.sql
SELECT task_id, DECODE(guid_ready, NULL, guid, guid_ready) AS guid
FROM PIKALKA.pass_task 
WHERE guid = :guid