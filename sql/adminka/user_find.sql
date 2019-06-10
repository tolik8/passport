-- adminka/user_find.sql
SELECT guid AS "data",
       viddil_id || ' ' || fio1 || ' ' || fio2 || ' ' || fio3 || DECODE(kadry_id, 1, '', ' (' || d.name || ')') AS "value"
FROM PIKALKA.people p, PIKALKA.d_kadry d
WHERE p.kadry_id = d.id AND
    UPPER(p.viddil_id || ' ' || p.fio1 || ' ' || p.fio2 || ' ' || p.fio3) LIKE UPPER('%' || :find || '%')
ORDER BY 2