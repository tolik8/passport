-- adminka/get_passport_access
SELECT t.id, t.name, DECODE(a.task_id, NULL, '', 'checked') checked
FROM pass_task t,
     (SELECT * FROM pass_access WHERE guid = :guid) a
WHERE t.id = a.task_id(+)
ORDER BY t.id