-- passport/insert/pov.sql
INSERT INTO PIKALKA.pasp_pov_old
SELECT :guid, pin, name, SUM(t) t, 
    CASE SUM(t) 
        WHEN 1 THEN 'Директор'
        WHEN 2 THEN 'Бухгалтер'
        WHEN 3 THEN 'Дир,Бух'
        WHEN 4 THEN 'Засновник'
        WHEN 5 THEN 'Дир,Зас'
        WHEN 6 THEN 'Бух,Зас'
        WHEN 7 THEN 'Дир,Бух,Зас'
        WHEN 8 THEN 'Засновник'
        ELSE 'Помилка'
    END typ
FROM
    (SELECT LPAD(pin,10,'0') pin, name, c_post t 
    FROM RG02.r21manager WHERE c_distr = :c_distr AND tin = :tin
    
    UNION SELECT LPAD(pin_found,10,'0'), name, 4 
    FROM RG02.r21pfound WHERE c_distr = :c_distr AND tin = :tin AND pin_found IS NOT NULL
    
    UNION SELECT TO_CHAR(tin_found), name, 8 
    FROM RG02.r21pfound WHERE c_distr = :c_distr AND tin = :tin AND tin_found IS NOT NULL)
GROUP BY pin, name