-- pasport/insert/pributok.sql
INSERT INTO PIKALKA.pasp_pributok
SELECT :guid, d.period_year,d.period_month,d.c_sti,d.tin,d.d_get,d.n_reg,
CASE WHEN z=1 THEN c_doc_sub||' '||'звітна' ELSE (CASE WHEN zn=1 THEN c_doc_sub||' '||'нова звітна' ELSE c_doc_sub||' '||'уточнююча' END)  END typ, 
P01  
FROM 
(select a.period_year,a.period_month,c_doc_sub,a.c_sti,a.tin,a.d_get,n_reg,
sum(decode(RTRIM(c.C_DOC_ROWC),'^HZ',c.zn,0)) as z,
sum(decode(RTRIM(c.C_DOC_ROWC),'^HZN',c.zn,0)) as zn,
sum(decode(RTRIM(c.C_DOC_ROWC),'^HZU',c.zn,0)) as ut,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R001G3',c.zn,0))/1000 as P01
FROM t_zregdoc a, t_zdata_N c
  where
     a.c_doc ='J01'
    and a.c_doc_sub IN('081','001')
    and a.period_year > 2014
    AND period_month=12
    and a.cod_regdoc=c.cod_regdoc(+)
    AND tin = :tin ----24633690,291569
and BITAND(a.flags,16)=0 and BITAND(a.flags,2048)=0 and bitand(a.flags,1048576)=0 and bitand(a.flags,134217728)=0
group by a.period_year,a.period_month,a.c_doc_sub,a.C_STI,a.tin,a.d_get,n_reg)d
--ORDER BY period_year,period_month,d_get
