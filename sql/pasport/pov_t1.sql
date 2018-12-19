-- pasport/pov_t1.sql
SELECT r.c_distr, r.tin, r.name, r.c_stan, '' blank
FROM RG02.r21taxpay r WHERE c_stan NOT IN (17,27) AND r.tin IN 
    (SELECT tin FROM RG02.r21pfound WHERE LPAD(kod_found,10,'0') = LPAD(:tin,10,'0'))
ORDER BY r.c_distr, r.tin