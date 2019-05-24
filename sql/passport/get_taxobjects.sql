-- passport/get_taxobjects.sql
SELECT t.name_obj, o.name, TO_DATE(o.d_acc_start) d_acc_start, TO_DATE(o.d_acc_end) d_acc_end, o.address adr_ns 
FROM KYIV.taxobjects o, e_type_obj t
WHERE o.tin = :tin
    AND o.to_type = t.type(+)
    AND (o.d_acc_end = '31.12.2999' OR o.d_acc_end IS NULL)
ORDER BY o.d_acc_start, o.name