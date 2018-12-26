-- pasport/insert/pributok.sql
INSERT INTO PIKALKA.pasp_pributok_old
SELECT :guid, d.period_year, d.period_month, d.c_sti, d.tin, d.d_get, d.n_reg,
    CASE WHEN z = 1 THEN c_doc_sub||' '||'звітна' 
        ELSE (CASE WHEN zn = 1 THEN c_doc_sub||' '||'нова звітна' ELSE c_doc_sub||' '||'уточнююча' END) END typ, 
     p01, p03, p04, p06,
    CASE WHEN p01 > 0 THEN ROUND((NVL(p06, 0) / NVL(p01, 0) * 100), 3) ELSE 0 END viddacha
FROM 
    (SELECT a.period_year, a.period_month, c_doc_sub, a.c_sti, a.tin, a.d_get, n_reg,
        SUM(DECODE(RTRIM(c.c_doc_rowc), '^HZ', c.zn, 0)) AS z,
        SUM(DECODE(RTRIM(c.c_doc_rowc), '^HZN', c.zn, 0)) AS zn,
        SUM(DECODE(RTRIM(c.c_doc_rowc), '^HZU', c.zn, 0)) AS ut,
        SUM(DECODE(RTRIM(c.c_doc_rowc), '^R001G3', c.zn, 0)) / 1000 AS p01,
        SUM(DECODE(RTRIM(c.c_doc_rowc), '^R003G3', c.zn, 0)) / 1000 AS p03,
        SUM(DECODE(RTRIM(c.c_doc_rowc), '^R004G3', c.zn, 0)) / 1000 AS p04,
        SUM(DECODE(RTRIM(c.c_doc_rowc), '^R006G3', c.zn, 0)) / 1000 AS p06
    FROM DP00.t_zdata_n c,
        (SELECT * FROM DP00.t_zregdoc WHERE tin = :tin
            AND c_doc = 'J01' AND c_doc_sub IN ('081', '001')
            AND period_year > 2014
            AND period_month = 12
            AND BITAND(flags, 16) = 0 AND BITAND(flags, 2048) = 0 AND BITAND(flags, 1048576) = 0 AND BITAND(flags, 134217728) = 0
        ) a
    WHERE a.cod_regdoc = c.cod_regdoc
    GROUP BY a.period_year, a.period_month, a.c_doc_sub, a.c_sti, a.tin, a.d_get, n_reg
    ) d