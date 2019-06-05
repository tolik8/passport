-- passport/get_1df.sql
SELECT year, ozn_dox, cnt, ROUND(dox, 0) dox, '' blank
FROM DP00.t43_1df_ozn
WHERE kod = :kod
ORDER BY YEAR, ozn_dox