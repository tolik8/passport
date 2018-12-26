-- pasport/insert/esv.sql
INSERT INTO PIKALKA.pasp_esv_old
select :guid, PIKALKA.year_mon(a.period_year, a.period_month) period,
sum(decode(RTRIM(c.C_DOC_ROWC),'^R01G3',round(c.zn,0.00),0)) as r1,    
sum(decode(RTRIM(c.C_DOC_ROWC),'^HNACTL3',c.zn,0.00)) as shtat,
CASE WHEN sum(decode(RTRIM(c.C_DOC_ROWC),'^HNACTL3',c.zn,0.00))=0 THEN NULL ELSE
    round(NVL(sum(decode(RTRIM(c.C_DOC_ROWC),'^R01G3',c.zn,0.00)),0)/NVL(sum(decode(RTRIM(c.C_DOC_ROWC),'^HNACTL3',c.zn,0.00)),0),0) END  ser_zp  
FROM DP00.t_zregdoc a, DP00.t_zdata_N c
  where
     a.c_doc in('J30')
    and a.c_doc_sub  in ('401')
  AND tin = :tin
  and a.period_year>=2016
  and a.cod_regdoc=c.cod_regdoc
  and BITAND(a.flags,16)=0 and BITAND(a.flags,2048)=0 and bitand(a.flags,1048576)=0 and bitand(a.flags,134217728)=0
group by a.period_year,a.period_month
--ORDER BY a.period_year,a.period_month