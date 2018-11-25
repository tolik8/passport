-- pasport/kontragent_kredit.sql
SELECT ROWNUM n, t.* FROM (
--SELECT d.c_sti_main sti, a.tin, d.name, obsiag_z_pdv obs, pdv, nom
SELECT 1918 sti, a.tin, 'Назва з PDV_ACT_R' name, obsiag_z_pdv obs, pdv, nom
FROM
(SELECT cp_tin, tin, ROUND(SUM(obsag), 0) obsiag_z_pdv,
    ROUND((SUM(obsag)*0.2),0) pdv,
    MIN(mind) || '-' || MAX(maxd) || ': ' || TO_CHAR(SUBSTR(PIKALKA.nom_to_line(:tin, tin, :dt1, :dt2), 1, 3977)) nom
FROM PIKALKA.knopka_s02
--WHERE dt0 = TO_DATE(SYSDATE) AND dt1 = :dt1 AND dt2 = :dt2 AND cp_tin = :tin
WHERE dt0 = '11.11.2018' AND dt1 = :dt1 AND dt2 = :dt2 AND cp_tin = :tin
GROUP BY cp_tin, tin) a--,
    --AISR.pdv_act_r_name d
--WHERE a.tin = d.tin(+)
ORDER BY obsiag_z_pdv DESC
) t