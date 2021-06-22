-- myuser/get_roles.sql
SELECT id FROM TOLIK.e_11roles WHERE block = 0 AND id IN
    (SELECT role_id FROM TOLIK.role_group WHERE group_id IN
        (SELECT group_id FROM TOLIK.group_user WHERE user_id = :guid))