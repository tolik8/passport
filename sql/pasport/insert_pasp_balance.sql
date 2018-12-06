-- pasport/insert_pasp_balance.sql
INSERT INTO PIKALKA.pasp_balance
SELECT :guid, C_STI,period_year,period_month,d_get,n_reg,
SUM(A1300) A1300,SUM(B1300) B1300,SUM(A1495) A1495, SUM(B1495) B1495, SUM(A1595) A1595, SUM(B1595) B1595,SUM(A1695) A1695,SUM(B1695) B1695,
SUM(A1700) A1700,SUM(B1700) B1700,SUM(A1800) A1800,SUM(B1800) B1800,SUM(A1900) A1900,SUM(B1900) B1900
FROM 
((select a.cod_regdoc,a.c_sti,a.tin,a.n_reg,a.d_get,d_term,a.period_year,a.period_month,c_doc,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1300',to_number(replace(zn, '.', ',')),0)) as A1300,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1300',to_number(replace(zn, '.', ',')),0)) as B1300,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1495',to_number(replace(zn, '.', ',')),0)) as A1495,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1595',to_number(replace(zn, '.', ',')),0)) as B1595,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1595',to_number(replace(zn, '.', ',')),0)) as A1595,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1495',to_number(replace(zn, '.', ',')),0)) as B1495,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1695',to_number(replace(zn, '.', ',')),0)) as A1695,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1695',to_number(replace(zn, '.', ',')),0)) as B1695,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1700',to_number(replace(zn, '.', ',')),0)) as A1700,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1700',to_number(replace(zn, '.', ',')),0)) as B1700,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1800',to_number(replace(zn, '.', ',')),0)) as A1800,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1800',to_number(replace(zn, '.', ',')),0)) as B1800,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1900',to_number(replace(zn, '.', ',')),0)) as A1900,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1900',to_number(replace(zn, '.', ',')),0)) as B1900
FROM t_zregdoc a, t_zdata_n c
  where
     a.c_doc ='S01'
    and a.c_doc_sub = '001'
    and a.period_year >=2014 
    AND tin = :tin
    and a.cod_regdoc=c.cod_regdoc(+)
and BITAND(a.flags,16)=0 and BITAND(a.flags,2048)=0 and bitand(a.flags,1048576)=0 and bitand(a.flags,134217728)=0
group by a.cod_regdoc,a.C_STI,a.tin,a.n_reg,a.d_get,d_term,a.period_year,a.period_month,c_doc)

union

(select a.cod_regdoc,a.c_sti,a.tin,a.n_reg,a.d_get,d_term,a.period_year,a.period_month,c_doc,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1300',to_number(replace(zn, '.', ',')),0)) as A1300,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1300',to_number(replace(zn, '.', ',')),0)) as B1300,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1495',to_number(replace(zn, '.', ',')),0)) as A1495,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1495',to_number(replace(zn, '.', ',')),0)) as B1495,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1595',to_number(replace(zn, '.', ',')),0)) as B1595,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1595',to_number(replace(zn, '.', ',')),0)) as A1595,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1695',to_number(replace(zn, '.', ',')),0)) as A1695,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1695',to_number(replace(zn, '.', ',')),0)) as B1695,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1700',to_number(replace(zn, '.', ',')),0)) as A1700,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1700',to_number(replace(zn, '.', ',')),0)) as B1700,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1800',to_number(replace(zn, '.', ',')),0)) as A1800,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1800',to_number(replace(zn, '.', ',')),0)) as B1800,
sum(decode(RTRIM(c.C_DOC_ROWC),'^A1900',to_number(replace(zn, '.', ',')),0)) as A1900,
sum(decode(RTRIM(c.C_DOC_ROWC),'^B1900',to_number(replace(zn, '.', ',')),0)) as B1900
FROM t_zregdoc a, t_zdata_c c
  where
     a.c_doc ='S01'
    and a.c_doc_sub = '001'
     and a.period_year >=2014 
    AND tin = :tin
    and a.cod_regdoc=c.cod_regdoc(+)
and BITAND(a.flags,16)=0 and BITAND(a.flags,2048)=0 and bitand(a.flags,1048576)=0 and bitand(a.flags,134217728)=0
group by a.cod_regdoc,a.period_year,a.period_month,a.C_STI,a.tin,a.n_reg,a.d_get,d_term,c_doc)

UNION

(select a.cod_regdoc,a.c_sti,a.tin,a.n_reg,a.d_get,d_term,a.period_year,a.period_month,c_doc,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1300G3',c.zn,0))as A1300,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1300G4',c.zn,0))as B1300,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1495G3',c.zn,0))as A1495,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1495G4',c.zn,0))as B1495,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1595G3',c.zn,0))as A1595,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1595G4',c.zn,0))as B1595,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1695G3',c.zn,0))as A1695,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1695G4',c.zn,0))as B1695,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1700G3',c.zn,0))as A1700,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1700G4',c.zn,0))as B1700,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1800G3',c.zn,0))as A1800,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1800G4',c.zn,0))as B1800,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1900G3',c.zn,0))as A1900,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R1900G4',c.zn,0))as B1900

FROM t_zregdoc a, t_zdata_n c
  where
     a.c_doc ='J09'
    and a.c_doc_sub = '001'
     and a.period_year >=2014  
    AND tin = :tin
    and a.cod_regdoc=c.cod_regdoc
and BITAND(a.flags,16)=0 and BITAND(a.flags,2048)=0 and bitand(a.flags,1048576)=0 and bitand(a.flags,134217728)=0
group by a.cod_regdoc,a.period_year,a.period_month,a.C_STI,a.tin,a.n_reg,a.d_get,d_term,c_doc))
group by cod_regdoc,C_STI,tin,n_reg,d_get,period_year,period_month,c_doc
--ORDER BY tin,period_year,period_month