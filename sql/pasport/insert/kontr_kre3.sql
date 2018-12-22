-- pasport/insert/kontr_kre3.sql
INSERT INTO PIKALKA.pasp_kontr_kre3
SELECT :guid, d.c_sti_main sti, a.tin, d.name, obsiag_z_pdv obs, pdv, nom
FROM
(SELECT cp_tin, tin, ROUND(SUM(obsag), 0) obsiag_z_pdv,
    ROUND((SUM(obsag)/6),0) pdv,
    MIN(mind) || '-' || MAX(maxd) || ': ' || TO_CHAR(SUBSTR(PIKALKA.nom_to_line_k(tin, :guid), 1, 3977)) nom
FROM PIKALKA.pasp_kontr_kre2
WHERE guid = :guid
GROUP BY cp_tin, tin) a,
    AISR.pdv_act_r_name d
WHERE a.tin = d.tin(+)