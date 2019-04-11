-- passport/founders.sql
SELECT sum_infund, kod_found, f.name, '' blank --ROUND(sum_infund * 100 / :sum_infund, 0) vids
FROM RG02.r21pfound f
WHERE tin = :tin --AND sum_infund > :sum_infund / 10
ORDER BY n_fndr