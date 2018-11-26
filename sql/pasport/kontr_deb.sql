-- pasport/kontr_deb.sql
SELECT ROWNUM n, t.* FROM (
SELECT d.c_sti_main sti, a.tin, d.name, obsiag_z_pdv obs, pdv, nom
FROM
(SELECT cp_tin, tin, ROUND(SUM(obsag), 0) obsiag_z_pdv,
    ROUND((SUM(obsag)*0.2),0) pdv,
    MIN(mind) || '-' || MAX(maxd) || ': ' || TO_CHAR(SUBSTR(PIKALKA.nom_to_line(tin, :guid), 1, 3977)) nom
FROM PIKALKA.pasp_kontr_deb2
WHERE guid = :guid
GROUP BY cp_tin, tin) a,
    AISR.pdv_act_r_name d
WHERE a.tin = d.tin(+)
ORDER BY obsiag_z_pdv DESC
) t