-- passport/get_work_info.sql
SELECT i.name, w.tm
FROM PIKALKA.pass_work w, PIKALKA.d_pass_info i
WHERE w.work_id = i.id AND w.guid = :guid
ORDER BY w.work_id