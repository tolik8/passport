CREATE OR REPLACE FUNCTION get_dpi_by_tin (in_tin NUMBER) RETURN NUMBER IS FResult NUMBER;
BEGIN
    SELECT c_distr INTO FResult FROM (
        SELECT c_distr
        FROM RG02.r21taxpay r,U_2900Z.d_stan s
        WHERE tin = in_tin AND r.c_stan = s.c_stan(+)
        ORDER BY s.sort
    ) WHERE ROWNUM = 1;

    RETURN FResult;
END get_dpi_by_tin;
