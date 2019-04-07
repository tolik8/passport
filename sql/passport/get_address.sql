-- passport/get_address.sql
SELECT AISR.rpp_util.getfulladdress(c_city,t_street,c_street,house,house_add,unit,apartment) adr
FROM RG02.r21paddr
WHERE tin = :tin AND c_distr = :c_distr AND c_adr = 1