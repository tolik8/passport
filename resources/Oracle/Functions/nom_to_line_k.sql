CREATE OR REPLACE FUNCTION nom_to_line_k (in_tin NUMBER, in_guid VARCHAR2) RETURN CLOB IS FResult CLOB;
BEGIN
  SELECT REPLACE(sys_xmlagg(XMLELEMENT(col, nom||' || ')).extract('/ROWSET/COL/text()').getclobval(), ';', '"') INTO FResult
  FROM (
     SELECT nom, obsag
     FROM PIKALKA.pasp_kontr_kre2
     WHERE guid = in_guid AND tin = in_tin
     ORDER BY obsag DESC
  );
  RETURN FResult;
END nom_to_line_k;
