-- passport/area_group_year.sql
SELECT period_year, c_sti,
    SUM(ril_plo) AS ril_plo,
    SUM(sin_plo) AS sin_plo,
    SUM(pas_plo) AS pas_plo,
    SUM(bag_plo) AS bag_plo,
    SUM(vod_plo) AS vod_plo,
    SUM(ril_plo) + SUM(sin_plo) + SUM(pas_plo) + SUM(bag_plo) + SUM(vod_plo) AS all_plo,
    '' AS blank
FROM PIKALKA.pass_area 
WHERE GUID = :guid
GROUP BY period_year, c_sti
ORDER BY period_year