-- get_passport_acceess
SELECT i.id, i.name, DECODE(a.work_id, NULL, '', 'checked') checked
FROM PIKALKA.d_pass_info i,
     (SELECT * FROM PIKALKA.pass_access WHERE guid = :guid) a
WHERE i.id = a.work_id(+)
ORDER BY i.id