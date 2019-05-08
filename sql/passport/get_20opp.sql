-- passport/get_20opp.sql
SELECT d.name_obj, o.using, o.address, o.d_reg, o.d_sks
FROM rg02.r21pobject o, e_type_obj d
WHERE o.c_type = d.kd(+)
    AND o.tin = :tin
ORDER BY name_obj, address