-- pasport/get_r21manager.sql
SELECT m.c_post, m.pin, m.name, m.n_tel
FROM RG02.r21manager m
WHERE m.tin = :tin