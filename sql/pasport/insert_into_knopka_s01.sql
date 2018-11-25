-- pasport/insert_into_knopka_s01.sql
INSERT INTO PIKALKA.knopka_s01
SELECT TO_DATE(SYSDATE) dt0, :dt1 dt1, :dt2 dt2, :guid GUID,
    a.code, a.tin, a.cp_tin, a.crtdate, b.RG3S_D2RG3S, SUBSTR(b.RG3S_D2RG3S,1,5) nom_sk, b.rg010
FROM 
    (SELECT * FROM ANALIZ.erpn_hd WHERE ftype = 0 AND cp_tin = :tin
        AND crtdate >= :dt1 AND crtdate <= :dt2) a, 
    (SELECT * FROM ANALIZ.erpn_bd WHERE nncode IN (
        SELECT code FROM ANALIZ.erpn_hd WHERE ftype = 0 AND cp_tin = :tin
            AND crtdate >= :dt1 AND crtdate <= :dt2) ) b
WHERE a.code = b.nncode