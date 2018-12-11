-- pasport/insert/kontr_kre1.sql
INSERT INTO PIKALKA.pasp_kontr_kre1
SELECT :guid, a.code, a.tin, a.cp_tin, a.crtdate, b.RG3S_D2RG3S, SUBSTR(b.RG3S_D2RG3S,1,5) nom_sk, b.rg010
FROM 
    (SELECT * FROM ANALIZ.erpn_hd WHERE ftype = 0 AND cp_tin = :tin
        AND crtdate >= :dt1 AND crtdate <= :dt2) a, 
    (SELECT * FROM ANALIZ.erpn_bd WHERE nncode IN (
        SELECT code FROM ANALIZ.erpn_hd WHERE ftype = 0 AND cp_tin = :tin
            AND crtdate >= :dt1 AND crtdate <= :dt2) ) b
WHERE a.code = b.nncode