-- pasport/insert/pov_t4.sql
INSERT INTO PIKALKA.pasp_pov_t4
SELECT p.*, 8 c_post, 'Засновник' post_name, 
    r.c_distr, r.tin, r.name ur_name, r.c_stan
FROM RG02.r21pfound f, RG02.r21taxpay r,
    (SELECT * FROM PIKALKA.pasp_pov WHERE guid = :guid) p
WHERE LPAD(f.tin_found,10,'0') = LPAD(p.pin,10,'0')
    AND f.tin = r.tin
    AND f.tin != :tin AND r.tin != :tin AND r.c_stan NOT IN (17,27,37)