-- passport/get_work_info.sql
SELECT 0 work_id, tm FROM PIKALKA.pass_jrn WHERE guid = :guid
UNION SELECT work_id, tm FROM PIKALKA.pass_work WHERE guid = :guid
ORDER BY work_id