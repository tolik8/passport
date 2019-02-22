-- myuser/get_roles.sql
SELECT id FROM PIKALKA.d_roles WHERE block = 0 AND id IN 
    (SELECT role_id FROM PIKALKA.role_group WHERE group_id IN 
        (SELECT group_id FROM PIKALKA.group_user WHERE user_id = :guid))