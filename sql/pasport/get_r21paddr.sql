-- pasport/get_r21paddr.sql
SELECT AISR.rpp_util.getfulladdress(a.c_city, a.t_street, a.c_street, a.house, a.house_add, a.unit, a.apartment) adresa
FROM RG02.r21paddr a
WHERE a.tin = :tin AND a.c_adr = 1