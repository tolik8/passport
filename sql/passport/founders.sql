-- founders
SELECT kod_found, NAME, sum_infund, ROUND(sum_infund * 100 / :sum_infund, 0) vids
FROM rg02.r21pfound
WHERE tin = :tin AND sum_infund > :sum_infund / 10
ORDER BY sum_infund DESC