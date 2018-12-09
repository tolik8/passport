create or replace function GET_DPI_BY_TIN(in_tin NUMBER) RETURN NUMBER IS FResult NUMBER(4);
begin
    SELECT c_distr INTO FResult FROM 
        (SELECT c_distr FROM RG02.r21taxpay WHERE tin = in_tin ORDER BY c_stan)
    WHERE ROWNUM = 1;
    
    return(FResult);
end GET_DPI_BY_TIN;