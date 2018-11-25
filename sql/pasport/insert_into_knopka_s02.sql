-- pasport/insert_into_knopka_s02.sql
INSERT INTO PIKALKA.knopka_s02
SELECT :guid GUID, TO_DATE(SYSDATE) dt0, :dt1 dt1, :dt2 dt2,
    cp_tin, tin,
    MIN(crtdate) mind,
    MAX(crtdate) maxd,
    ROUND((SUM(rg010)+(SUM(rg010)*0.2))/1000,0) obsag,
    ROUND((SUM(rg010)+(SUM(rg010)*0.2))/1000,0) || ' тис ' || '(' || COUNT(*) || ') ' ||
    PIKALKA.get_one_row_from_nom(tin, :dt1, :dt2, nom_sk) nom
FROM PIKALKA.knopka_s01
WHERE dt0 = TO_DATE(SYSDATE) AND dt1 = :dt1 AND dt2 = :dt2 AND cp_tin = :tin
GROUP BY cp_tin, tin, nom_sk