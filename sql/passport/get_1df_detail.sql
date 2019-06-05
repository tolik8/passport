-- passport/get_1df_detail.sql
SELECT DISTINCT d.zy, d.zkv, ROUND(d.gr05, 0) ozn_dox, ROUND(d.gr03a, 0) narax, d.tin_drfo, p.name, d.gr09
FROM IVASYKB.pasp_1df d, RG02.r21taxpay p
WHERE d.tin = :tin
    AND d.tin_drfo = p.tin(+)
MINUS
SELECT v.* FROM
    (
    SELECT DISTINCT d.zy, d.zkv, ROUND(d.gr05, 0) ozn_dox, ROUND(d.gr03a, 0) narax, d.tin_drfo, p.name, d.gr09
    FROM IVASYKB.pasp_1df d, RG02.r21taxpay p
    WHERE d.tin = :tin
        AND d.tin_drfo = p.tin(+)
    ) v,

    (
    SELECT DISTINCT d.zy, d.zkv, ROUND(d.gr05, 0) ozn_dox, ROUND(d.gr03a, 0) narax, d.tin_drfo, p.name, d.gr09
    FROM IVASYKB.pasp_1df d, RG02.r21taxpay p
    WHERE d.tin = :tin
        AND d.tin_drfo = p.tin(+) AND gr09 = 1
    ) v1

    WHERE v.zy = v1.zy
        AND v.zkv = v1.zkv
        AND v.ozn_dox = v1.ozn_dox
        AND v.narax = v1.narax
        AND v.tin_drfo = v1.tin_drfo
