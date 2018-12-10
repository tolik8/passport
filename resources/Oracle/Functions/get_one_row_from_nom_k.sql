CREATE OR REPLACE FUNCTION get_one_row_from_nom_k (in_guid VARCHAR2, in_nom_sk VARCHAR2) RETURN VARCHAR2 IS FResult VARCHAR2(500);
BEGIN
    SELECT SUBSTR(RG3S_D2RG3S,0,500) INTO FResult
    FROM PIKALKA.pasp_kontr_kre1
    WHERE guid = in_guid
        AND nom_sk = in_nom_sk 
        AND ROWNUM = 1;

    RETURN FResult;
END get_one_row_from_nom_k;
