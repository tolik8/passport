-- pasport/insert/pov.sql
INSERT INTO PIKALKA.pasp_pov
SELECT :guid, pin, name, SUM(t) t, 
    CASE SUM(t) 
        WHEN 1 THEN 'Директор'
        WHEN 2 THEN 'Гол.Бухгалтер'
        WHEN 3 THEN 'Директор, Гол.Бухгалтер'
        WHEN 4 THEN 'Засновник'
        WHEN 5 THEN 'Директор, Засновник'
        WHEN 6 THEN 'Гол.Бухгалтер, Засновник'
        WHEN 7 THEN 'Директор, Гол.Бухгалтер, Засновник'
        WHEN 8 THEN 'Засновник'
        ELSE 'Помилка'
    END typ
FROM
    (SELECT LPAD(pin,10,'0') pin, name, c_post t 
    FROM RG02.r21manager WHERE c_distr = :c_distr AND tin = :tin
    
    UNION SELECT LPAD(pin_found,10,'0'), name, 4 
    FROM RG02.r21pfound WHERE c_distr = :c_distr AND tin = :tin AND pin_found IS NOT NULL
    
    UNION SELECT LPAD(tin_found,10,'0'), name, 8 
    FROM RG02.r21pfound WHERE c_distr = :c_distr AND tin = :tin AND tin_found IS NOT NULL)
GROUP BY pin, name