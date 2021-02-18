-- Формується паспорт
SELECT kadry_id, viddil_id, PIKALKA.fio(p.fio1, p.fio2, p.fio3) fio, t.this_date, ROUND(t.total_time) AS total_time, t.what
FROM PIKALKA.people p,
    (SELECT SUBSTR(what,8,32) GUID, this_date, total_time, what FROM user_jobs) t
WHERE p.guid = t.guid
ORDER BY total_time DESC