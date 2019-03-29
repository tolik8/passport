-- passport/access_works.sql
SELECT i.* 
FROM PIKALKA.d_pass_info i,
    (SELECT * FROM PIKALKA.pass_access WHERE guid = :guid) a
WHERE i.id = a.work_id
ORDER BY i.id