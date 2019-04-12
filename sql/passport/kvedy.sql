-- passport/kvedy.sql
SELECT k.kved, d.nu, '' AS blank
FROM RG02.r21pkved k, ETALON.e_kved d
WHERE k.kved = d.kod AND k.tin = :tin AND k.is_main = 0
ORDER BY kved