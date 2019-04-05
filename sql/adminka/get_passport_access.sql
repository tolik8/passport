-- get_passport_acceess
SELECT t.id, t.name, DECODE(a.task_id, NULL, '', 'checked') checked
FROM PIKALKA.d_pass_task t,
     (SELECT * FROM PIKALKA.pass_access WHERE guid = :guid) a
WHERE t.id = a.task_id(+)
ORDER BY t.id