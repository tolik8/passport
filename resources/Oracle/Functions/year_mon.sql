CREATE OR REPLACE FUNCTION year_mon (in_year IN NUMBER, in_month IN NUMBER) RETURN VARCHAR2
IS
BEGIN
    RETURN TO_CHAR(in_year) || '-' || TRIM(TO_CHAR(in_month, '00'));
END;