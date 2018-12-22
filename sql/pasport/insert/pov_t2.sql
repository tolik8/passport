-- pasport/insert/pov_t2.sql
INSERT INTO PIKALKA.pasp_pov_t2
SELECT p.*, m.c_post,
    DECODE(m.c_post, 1, 'Директор', 2, 'Бухгалтер', 'Інше') post_name, 
    r.c_distr, r.tin, r.name ur_name, r.c_stan
FROM RG02.r21manager m, RG02.r21taxpay r,
    (SELECT * FROM PIKALKA.pasp_pov WHERE guid = :guid) p
WHERE LPAD(m.pin,10,'0') = LPAD(p.pin,10,'0')
    AND m.tin = r.tin
    AND m.tin != :tin AND r.tin != :tin AND r.c_stan NOT IN (17,27,37)