-- passport/area_group_year_dpi.sql
SELECT period_year, c_sti, 
    SUM(ril_plo) ril_plo,
    SUM(sin_plo) sin_plo,
    SUM(pas_plo) pas_plo,
    SUM(bag_plo) bag_plo,
    SUM(vod_plo) vod_plo,
    SUM(ril_plo) + SUM(sin_plo) + SUM(pas_plo) + SUM(bag_plo) + SUM(vod_plo) AS all_plo,
    '' AS blank
FROM PIKALKA.pass_area 
WHERE GUID = :guid
GROUP BY period_year, c_sti
ORDER BY period_year, c_sti