-- adminka/get_user.sql
SELECT u.login, u.viddil_id, v.name AS viddil_name,
    u.fio1, u.fio2, u.fio3, k.name AS place,
    p.name posada, u.kab, u.phone_ip
FROM PIKALKA.people u,
     TOLIK.e_11staff k,
     TOLIK.e_11department v,
     TOLIK.e_11position p
WHERE u.guid = :guid
    AND u.kadry_id = k.id(+)
    AND u.viddil_id = v.id(+)
    AND u.posada_id = p.id(+)