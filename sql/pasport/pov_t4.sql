-- pasport/pov_t4.sql
SELECT r.c_distr, r.tin, r.name fop_name, r.c_stan, 'тно' post_name, p.*
FROM RG02.r21taxpay r,
    (SELECT * FROM PIKALKA.pasp_pov WHERE guid = :guid) p
WHERE LPAD(p.pin,10,'0') = LPAD(r.tin,10,'0')
    AND r.tin != :tin AND r.c_stan NOT IN (17,27) AND r.face_mode = 2
ORDER BY p.t DESC, r.tin