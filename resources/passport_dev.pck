CREATE OR REPLACE PACKAGE passport_dev IS

    DEBUG$ BOOLEAN := FALSE;

    FUNCTION get_one_row_from_nom_k (guid$ VARCHAR2, nom_sk$ VARCHAR2) RETURN VARCHAR2;
    FUNCTION get_one_row_from_nom_z (guid$ VARCHAR2, nom_sk$ VARCHAR2) RETURN VARCHAR2;
    FUNCTION nom_to_line_k (tin$ NUMBER, guid$ VARCHAR2) RETURN CLOB;
    FUNCTION nom_to_line_z (tin$ NUMBER, guid$ VARCHAR2) RETURN CLOB;

    PROCEDURE truncate_all_tables;

    PROCEDURE delete_old_data;
    PROCEDURE delete_my_data;
    PROCEDURE delete_by_guid (guid$ VARCHAR2);

    PROCEDURE prepare (tin$ NUMBER, dt1$ VARCHAR2, dt2$ VARCHAR2, works$ VARCHAR2, refresh$ VARCHAR2, guid_user$ VARCHAR2, guid$ VARCHAR2 DEFAULT '');

    PROCEDURE create_job (tin$ NUMBER, dt1$ VARCHAR2, dt2$ VARCHAR2, works$ VARCHAR2, refresh$ VARCHAR2, guid_user$ VARCHAR2, guid$ VARCHAR2 DEFAULT NULL);

END;
/

CREATE OR REPLACE PACKAGE BODY passport_dev IS

    START_TIME# TIMESTAMP;
    SQL_TEXT#   CLOB;
    GUID#       VARCHAR2(32);
    GUID_USER#  GUID#%TYPE;
    WORKS#      VARCHAR2(50);
    --REFRESH#    VARCHAR2(50);
    C_DISTR#    NUMBER;
    TIN#        NUMBER;
    DT1#        VARCHAR2(12);
    DT2#        DT1#%TYPE;
    --STEP#       NUMBER;
    CR#         VARCHAR2(2) := CHR(13) || CHR(10);

    /* обгорнути в одинарні лапки */
    FUNCTION q (input$ VARCHAR) RETURN VARCHAR2
    IS
    BEGIN
        RETURN '''' || input$ || '''';
    END;

    /* склеїти рік і місяць */
    FUNCTION year_mon (year$ IN NUMBER, month$ IN NUMBER) RETURN VARCHAR2
    IS
    BEGIN
        RETURN TO_CHAR(year$) || '-' || TRIM(TO_CHAR(month$, '00'));
    END;

    /* різниця між датами в секундах */
    FUNCTION timestamp_diff (t1$ TIMESTAMP, t2$ TIMESTAMP) RETURN NUMBER
    IS
    BEGIN
        RETURN EXTRACT (DAY    FROM (t2$ - t1$))*24*60*60+
               EXTRACT (HOUR   FROM (t2$ - t1$))*60*60+
               EXTRACT (MINUTE FROM (t2$ - t1$))*60+
               EXTRACT (SECOND FROM (t2$ - t1$));
    END;

    /* перевірка чи не запущена в даний момент процедура prepare по вказаному платнику за вказаний період */
    FUNCTION check_started RETURN NUMBER
    IS count# NUMBER;
    BEGIN
        SELECT COUNT(*) INTO count# FROM PIKALKA.pass_jrn
        WHERE tin = TIN# AND dt1 = DT1# AND dt2 = DT2# AND tm IS NULL;

        RETURN count#;
    END;

    FUNCTION get_c_distr_by_tin (tin$ NUMBER) RETURN NUMBER 
    IS FResult NUMBER;
    BEGIN
        BEGIN
            SELECT c_distr INTO FResult FROM (
                SELECT c_distr
                FROM RG02.r21taxpay r, U_2900Z.d_stan s
                WHERE tin = tin$ AND r.c_stan = s.c_stan(+)

                ORDER BY s.sort
            ) WHERE ROWNUM = 1;
        EXCEPTION
                WHEN OTHERS THEN
            RETURN 0;
        END;

        RETURN FResult;
    END;

    FUNCTION get_one_row_from_nom_k (guid$ VARCHAR2, nom_sk$ VARCHAR2) RETURN VARCHAR2 
    IS FResult VARCHAR2(500);
    BEGIN
        SELECT SUBSTR(RG3S_D2RG3S,0,500) INTO FResult
        FROM PIKALKA.pass_kontr_kredit_1
        WHERE guid = guid$
            AND nom_sk = nom_sk$
            AND ROWNUM = 1;

        RETURN FResult;
    END;

    FUNCTION get_one_row_from_nom_z (guid$ VARCHAR2, nom_sk$ VARCHAR2) RETURN VARCHAR2 
    IS FResult VARCHAR2(500);
    BEGIN
        SELECT SUBSTR(RG3S_D2RG3S,0,500) INTO FResult
        FROM PIKALKA.pass_kontr_zobov_1
        WHERE guid = guid$
            AND nom_sk = nom_sk$
            AND ROWNUM = 1;

        RETURN FResult;
    END;

    FUNCTION nom_to_line_k (tin$ NUMBER, guid$ VARCHAR2) RETURN CLOB 
    IS FResult CLOB;
    BEGIN
        SELECT REPLACE(sys_xmlagg(XMLELEMENT(col, nom||' || ')).extract('/ROWSET/COL/text()').getclobval(), ';', '"') INTO FResult
        FROM (
            SELECT nom, obsag
            FROM PIKALKA.pass_kontr_kredit_2
            WHERE guid = guid$ AND tin = tin$
            ORDER BY obsag DESC
        );
        RETURN FResult;
    END;

    FUNCTION nom_to_line_z (tin$ NUMBER, guid$ VARCHAR2) RETURN CLOB 
    IS FResult CLOB;
    BEGIN
        SELECT REPLACE(sys_xmlagg(XMLELEMENT(col, nom||' || ')).extract('/ROWSET/COL/text()').getclobval(), ';', '"') INTO FResult
        FROM (
            SELECT nom, obsag
            FROM PIKALKA.pass_kontr_zobov_2
            WHERE guid = guid$ AND cp_tin = tin$
            ORDER BY obsag DESC
        );
        RETURN FResult;
    END;
    
    PROCEDURE msg (input$ VARCHAR)
    IS
    BEGIN
        IF DEBUG$ THEN DBMS_OUTPUT.put_line(input$); END IF;
    END;

    /*PROCEDURE protocol (seconds$ NUMBER, total_seconds$ NUMBER, sql_name$ VARCHAR2, is_err$ NUMBER DEFAULT 0)
    IS
        PRAGMA AUTONOMOUS_TRANSACTION;
    BEGIN
        INSERT INTO PIKALKA.pass_steps (guid, tm, tm_total, step, NAME, is_err)
            VALUES (GUID#, seconds$, total_seconds$, STEP#, sql_name$, is_err$);
        COMMIT;
        STEP# := STEP# + 1;
    END;*/

    PROCEDURE error_to_log (mess$ CLOB DEFAULT NULL, type$ NUMBER DEFAULT 0)
    IS
        PRAGMA AUTONOMOUS_TRANSACTION;
        --id# NUMBER;
        mess# CLOB;
        type# NUMBER := 0;
    BEGIN
        mess# := mess$;
        IF INSTR(mess#, 'ORA-') > 0 then
            type# := -1;
        ELSE
            type# := type$;
        END IF;
        LOOP
            --SELECT seq_idevent.nextval INTO id# FROM dual;
            INSERT INTO PIKALKA.pass_errors(mess, typs) VALUES (SUBSTR(mess#, 1, 1000), type#);
            COMMIT;
            mess# := SUBSTR(mess#, 1001);
            IF NVL(LENGTH(mess#), 0) = 0 THEN EXIT; END IF;
        END LOOP;
    END;


    PROCEDURE execute_sql (sql_id$ NUMBER)
    IS
        --time_begin# TIMESTAMP;
        --seconds# NUMBER;
        --total_seconds# NUMBER;
        sql_name# VARCHAR2(50);
    BEGIN
        SELECT NAME, text INTO sql_name#, SQL_TEXT# FROM PIKALKA.d_sql WHERE project_id = 1 AND id = sql_id$;
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':guid', q(GUID#));
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':tin', TIN#);
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':dt1', q(DT1#));
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':dt2', q(DT2#));
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':c_distr', C_DISTR#);

        BEGIN
            --time_begin# := SYSTIMESTAMP;
            EXECUTE IMMEDIATE TO_CHAR(SQL_TEXT#);
            --seconds# := ROUND(timestamp_diff(time_begin#, SYSTIMESTAMP), 3);
            --total_seconds# := ROUND(timestamp_diff(START_TIME#, SYSTIMESTAMP), 3);
            --protocol(seconds#, total_seconds#, sql_name#);
            msg('Ok! ' || sql_name#);
        EXCEPTION
            WHEN OTHERS THEN
                --protocol(seconds#, total_seconds#, sql_name#, 1);
                msg('Error! '|| sql_name# || ' ' || SQLERRM);
                IF NOT DEBUG$ THEN
                   error_to_log(sql_name# || ' ' || SQLERRM);
                END IF;
        END;
    END;

    PROCEDURE session_start
    IS
        PRAGMA AUTONOMOUS_TRANSACTION;
    BEGIN
        INSERT INTO PIKALKA.pass_jrn (guid, dt1, dt2, tin, guid_user, works)
            VALUES (GUID#, DT1#, DT2#, TIN#, GUID_USER#, WORKS#);
        COMMIT;
    END;

    PROCEDURE session_end
    IS
        PRAGMA AUTONOMOUS_TRANSACTION;
        seconds# NUMBER;
    BEGIN
        /* визначаємо скільки часу тривала підготовка даних */
        seconds# := ROUND(timestamp_diff(START_TIME#, SYSTIMESTAMP), 0);
        msg('Seconds - ' || TO_CHAR(seconds#));
        --protocol(0, seconds#, 'Завершено');
        INSERT INTO PIKALKA.pass_log (guid, dt1, dt2, tin, guid_user, tm, prepared, works)
            VALUES (GUID#, DT1#, DT2#, TIN#, GUID_USER#, seconds#, 1, WORKS#);
        UPDATE PIKALKA.pass_jrn SET tm = seconds# WHERE guid = GUID#;
        COMMIT;
    END;

    PROCEDURE create_work(work$ VARCHAR2)
    IS
        PRAGMA AUTONOMOUS_TRANSACTION;
    BEGIN
        INSERT INTO PIKALKA.pass_work (guid, work_id, dt1, dt2, tin, guid_user)
        SELECT GUID#, id, DT1#, DT2#, TIN#, GUID_USER#
        FROM PIKALKA.d_pass_info
        WHERE in_comma_string(id, work$) = 1;
        COMMIT;
    END;

    PROCEDURE update_work(guid$ VARCHAR2, work_id$ NUMBER, seconds$ NUMBER)
    IS
        PRAGMA AUTONOMOUS_TRANSACTION;
    BEGIN
        UPDATE PIKALKA.pass_work SET tm = seconds$ WHERE guid = guid$ AND work_id = work_id$;
        COMMIT;
    END;

    PROCEDURE truncate_all_tables
    IS
        TYPE array_t IS VARRAY(3) OF VARCHAR2(30);
        ARRAY array_t := array_t('jrn', 'steps', 'errors');
        sql# VARCHAR2(500);
    BEGIN
        FOR i IN 1..array.count LOOP
            sql# := 'TRUNCATE TABLE PIKALKA.pass_' || ARRAY(i);
            --EXECUTE IMMEDIATE sql#;
        END LOOP;

        FOR cur IN (SELECT tbl FROM PIKALKA.d_sql WHERE project_id = 1 ORDER BY id) LOOP
            sql# := 'TRUNCATE TABLE PIKALKA.' || cur.tbl;
            BEGIN
                EXECUTE IMMEDIATE sql#;
            EXCEPTION
                WHEN OTHERS THEN
                    msg(sql# || CR# || SQLERRM);
            END;
        END LOOP;

        msg('TRUNCATE ALL TABLES');
    END;

    PROCEDURE delete_wrong_data
    IS
        sql# VARCHAR2(500);
    BEGIN
        FOR cur IN (SELECT tbl FROM PIKALKA.d_sql WHERE project_id = 1 ORDER BY id) LOOP
            sql# := 'DELETE FROM PIKALKA.' || cur.tbl || ' WHERE guid NOT IN (SELECT guid FROM PIKALKA.pass_jrn)';
            BEGIN
                EXECUTE IMMEDIATE sql#;
            EXCEPTION
                WHEN OTHERS THEN
                    msg(sql# || CR# || SQLERRM);
            END;
        END LOOP;
    END;

    PROCEDURE delete_old_data
    IS
    BEGIN
        DELETE FROM PIKALKA.pass_jrn WHERE dt0 < SYSDATE - 7;
        DELETE FROM PIKALKA.pass_steps WHERE guid NOT IN (SELECT guid FROM PIKALKA.pass_jrn);
        delete_wrong_data;
        COMMIT;
        msg('DELETE old data FROM ALL TABLES');
    END;

    PROCEDURE delete_my_data
    IS
    BEGIN
        DELETE FROM PIKALKA.pass_jrn WHERE guid='06F2EF58972B2E32E050130A64136A5F' OR tin=300400;
        DELETE FROM PIKALKA.pass_steps WHERE guid NOT IN (SELECT guid FROM PIKALKA.pass_jrn);
        DELETE FROM PIKALKA.pass_work WHERE guid NOT IN (SELECT guid FROM PIKALKA.pass_jrn);
        delete_wrong_data;
        COMMIT;
        msg('DELETE my data FROM ALL TABLES');
    END;

    PROCEDURE delete_by_guid (guid$ VARCHAR2)
    IS
        sql# VARCHAR2(500);
    BEGIN
        DELETE FROM PIKALKA.pass_jrn WHERE guid = guid$;
        DELETE FROM PIKALKA.pass_steps WHERE guid = guid$;

        FOR cur IN (SELECT tbl FROM PIKALKA.d_sql WHERE project_id = 1 ORDER BY id) LOOP
            sql# := 'DELETE FROM PIKALKA.' || cur.tbl || ' WHERE guid = ''' || guid$ || '''';
            BEGIN
                EXECUTE IMMEDIATE sql#;
            EXCEPTION
                WHEN OTHERS THEN
                    msg(sql# || CR# || SQLERRM);
            END;
        END LOOP;

        COMMIT;
        msg('DELETE FROM ALL TABLES BY guid');
    END;

    PROCEDURE do_it(id$ NUMBER)
    IS
    BEGIN
        FOR cur IN (
            SELECT sql_id FROM PIKALKA.d_pass_work WHERE work_id = id$ ORDER BY sql_id
        ) LOOP
            execute_sql(cur.sql_id);
        END LOOP;
    END;

    PROCEDURE prepare (tin$ NUMBER, dt1$ VARCHAR2, dt2$ VARCHAR2, works$ VARCHAR2, refresh$ VARCHAR2, guid_user$ VARCHAR2, guid$ VARCHAR2 DEFAULT '')
    IS
        cnt# NUMBER;
        cnt_users# NUMBER;
        time_begin# TIMESTAMP;
        seconds# NUMBER;
    BEGIN
        /* засікаємо час початку процедури */
        START_TIME# := SYSTIMESTAMP;
        --STEP# := 0;

        TIN# := tin$;
        DT1# := dt1$;
        DT2# := dt2$;
        GUID_USER# := guid_user$;
        WORKS# := works$;

        SELECT COUNT(*) INTO cnt_users# FROM PIKALKA.people WHERE guid = guid_user$;

        IF cnt_users# != 1 THEN
            msg('USER BY GUID ' || guid_user$ || ' NOT FOUND');
            RETURN;
        END IF;

        cnt# := check_started;
        IF cnt# > 0 THEN msg('The procudure PASSPORT.PREPARE works at this time.'); RETURN; END IF;

        /* видаляємо всі старі дані по вказаному коду за вказаний період */
        --delete_old_data;

        /* шукаємо C_DISTR */
        C_DISTR# := get_c_distr_by_tin(tin$);
        IF C_DISTR# = 0 THEN msg('TAXPAYER ' || TO_CHAR(tin$) || ' NOT FOUND'); RETURN; END IF;

        /* Беремо GUID запиту з параметрів або створюємо новий */
        GUID# := NVL(guid$, SYS_GUID());
        msg('GUID - ' || GUID#);

        /* запис в журнал (який періодично очищається) */
        session_start;
        --protocol(0, 0, 'Початок');

        /* в таблицю pass_work вставити записи, яку саме роботу потрібно виконати (d_pass_info, d_pass_work) */
        create_work(works$);

        FOR cur IN (
            /* Список задач з довідника, враховуючи доступ */
            SELECT i.id, r.dt0, in_comma_string(i.id, refresh$) rf
            FROM PIKALKA.d_pass_info i, 
                (SELECT * FROM PIKALKA.pass_access WHERE guid = guid_user$) a,
                (SELECT work_id, MAX(dt0) dt0
                FROM PIKALKA.pass_work
                WHERE tin = tin$ AND dt1 = dt1$ AND dt2 = dt2$
                GROUP BY tin, dt1, dt2, work_id) r
            WHERE i.id = a.work_id AND i.id = r.work_id(+)
                AND in_comma_string(i.id, works$) = 1
            ORDER BY i.id
        ) LOOP
            time_begin# := SYSTIMESTAMP;
            --execute_sql(cur.id);
            --IF in_comma_string(cur.id, refresh$) = 1 THEN END IF;
--            IF cur.dt0 IS NULL OR cur.rf = 1 THEN
                do_it(cur.id);
--            ELSE
                -- update pass_work
--            END IF;
            seconds# := ROUND(timestamp_diff(time_begin#, SYSTIMESTAMP), 3);
            update_work(GUID#, cur.id, seconds#);
        END LOOP;

        /* запис в лог (не очищається ніколи) */
        session_end;

        DELETE FROM PIKALKA.pass_kontr_kredit_1 WHERE guid = GUID#;
        DELETE FROM PIKALKA.pass_kontr_kredit_2 WHERE guid = GUID#;
        DELETE FROM PIKALKA.pass_kontr_zobov_1 WHERE guid = GUID#;
        DELETE FROM PIKALKA.pass_kontr_zobov_2 WHERE guid = GUID#;

        COMMIT;
    END;

    PROCEDURE create_job (tin$ NUMBER, dt1$ VARCHAR2, dt2$ VARCHAR2, works$ VARCHAR2, refresh$ VARCHAR2, guid_user$ VARCHAR2, guid$ VARCHAR2 DEFAULT NULL)
    IS
        count#   NUMBER;
        job_n#   NUMBER := 0;
        job#     VARCHAR2(2000);
        pref#    VARCHAR2(10) := '--GUID=';
        package# VARCHAR2(32);
    BEGIN
        BEGIN
            SELECT COUNT(*) INTO count# FROM user_jobs j
            WHERE SUBSTR(j.what, 8, INSTR(j.what, CR#)-8) = guid_user$;
        EXCEPTION
            WHEN OTHERS THEN count# := 0;
        END;

        package# := $$PLSQL_UNIT;

        IF count# = 0 THEN
            job# := pref# || guid_user$ || CR# || 'BEGIN PIKALKA.' || package# || '.prepare('
                || tin$ || ', '
                || q(dt1$) || ', '
                || q(dt2$) || ', '
                || q(works$) || ', '
                || q(refresh$) || ', '
                || q(guid_user$) || ', '
                || q(guid$)
                || '); END;';
            msg(job#);
            DBMS_JOB.submit(job_n#, job#);
            COMMIT;
        END IF;
    END;

END;
/

