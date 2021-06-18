-- passport/get_tasks_guid.sql
SELECT task_id, DECODE(guid_ready, NULL, guid, guid_ready) AS guid
FROM pass_tasks
WHERE guid = :guid