CREATE OR REPLACE PACKAGE passport IS

    DEBUG$ BOOLEAN := FALSE;

    FUNCTION get_one_row_from_nom_k (guid$ VARCHAR2, nom_sk$ VARCHAR2) RETURN VARCHAR2;
    FUNCTION get_one_row_from_nom_z (guid$ VARCHAR2, nom_sk$ VARCHAR2) RETURN VARCHAR2;
    FUNCTION nom_to_line_k (tin$ NUMBER, guid$ VARCHAR2) RETURN CLOB;
    FUNCTION nom_to_line_z (tin$ NUMBER, guid$ VARCHAR2) RETURN CLOB;

    PROCEDURE truncate_all_tables;
    
    PROCEDURE delete_by_guid (guid$ VARCHAR2);

    PROCEDURE prepare (tin$ NUMBER, dt1$ VARCHAR2, dt2$ VARCHAR2, guid_user$ VARCHAR2, guid$ VARCHAR2 DEFAULT '');

    PROCEDURE create_job (tin$ NUMBER, dt1$ VARCHAR2, dt2$ VARCHAR2, guid_user$ VARCHAR2, guid$ VARCHAR2 DEFAULT NULL);
    
END;
/
CREATE OR REPLACE PACKAGE BODY passport IS

    START_TIME# TIMESTAMP;
    SQL_TEXT#   CLOB;
    GUID#       VARCHAR2(32);
    GUID_USER#  GUID#%TYPE;
    C_DISTR#    NUMBER;
    TIN#        NUMBER;
    DT1#        VARCHAR2(12);
    DT2#        DT1#%TYPE;
    STEP#       NUMBER;

    -- обгорнути в одинарні лапки
    FUNCTION q (input$ VARCHAR) RETURN VARCHAR2
    IS
    BEGIN
        RETURN '''' || input$ || '''';
    END;

    -- склеїти рік і місяць
    FUNCTION year_mon (year$ IN NUMBER, month$ IN NUMBER) RETURN VARCHAR2
    IS
    BEGIN
        RETURN TO_CHAR(year$) || '-' || TRIM(TO_CHAR(month$, '00'));
    END;

    -- різниця між датами в секундах
    FUNCTION timestamp_diff (t1$ TIMESTAMP, t2$ TIMESTAMP) RETURN NUMBER
    IS
    BEGIN
        RETURN EXTRACT (DAY    FROM (t2$ - t1$))*24*60*60+
               EXTRACT (HOUR   FROM (t2$ - t1$))*60*60+
               EXTRACT (MINUTE FROM (t2$ - t1$))*60+
               EXTRACT (SECOND FROM (t2$ - t1$));
    END;

    -- перевірка чи не запущена в даний момент процедура prepare по вказаному платнику за вказаний період
    FUNCTION check_started RETURN NUMBER
    IS count# NUMBER;
    BEGIN
        SELECT COUNT(*) INTO count# FROM PIKALKA.pass_jrn 
        WHERE tin = TIN# AND dt1 = DT1# AND dt2 = DT2# AND tm IS NULL;
        
        RETURN count#;
    END;
    
    FUNCTION get_one_row_from_nom_k (guid$ VARCHAR2, nom_sk$ VARCHAR2) RETURN VARCHAR2 IS FResult VARCHAR2(500);
    BEGIN
        SELECT SUBSTR(RG3S_D2RG3S,0,500) INTO FResult
        FROM PIKALKA.pass_kontr_kredit_1
        WHERE guid = guid$
            AND nom_sk = nom_sk$
            AND ROWNUM = 1;

        RETURN FResult;
    END;

    FUNCTION get_one_row_from_nom_z (guid$ VARCHAR2, nom_sk$ VARCHAR2) RETURN VARCHAR2 IS FResult VARCHAR2(500);
    BEGIN
        SELECT SUBSTR(RG3S_D2RG3S,0,500) INTO FResult
        FROM PIKALKA.pass_kontr_zobov_1
        WHERE guid = guid$
            AND nom_sk = nom_sk$
            AND ROWNUM = 1;

        RETURN FResult;
    END;
    
    FUNCTION nom_to_line_k (tin$ NUMBER, guid$ VARCHAR2) RETURN CLOB IS FResult CLOB;
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

    FUNCTION nom_to_line_z (tin$ NUMBER, guid$ VARCHAR2) RETURN CLOB IS FResult CLOB;
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
        DBMS_OUTPUT.put_line(input$);
    END;

    PROCEDURE protocol (seconds$ NUMBER, total_seconds$ NUMBER, sql_name$ VARCHAR2, is_err$ NUMBER DEFAULT 0)
    IS
        PRAGMA AUTONOMOUS_TRANSACTION;
    BEGIN
        INSERT INTO PIKALKA.pass_steps (guid, tm, tm_total, step, NAME, is_err) 
            VALUES (GUID#, seconds$, total_seconds$, STEP#, sql_name$, is_err$);
        COMMIT;
        STEP# := STEP# + 1;
    END;

    PROCEDURE error_to_log (mess$ CLOB DEFAULT NULL, type$ NUMBER DEFAULT 0)
    IS
        PRAGMA AUTONOMOUS_TRANSACTION;
        --id# NUMBER;
        mess# CLOB;
        type# NUMBER := 0;
    BEGIN
        mess# := mess$;
        IF instr(mess#, 'ORA-') > 0 then
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
        time_begin# TIMESTAMP;
        seconds# NUMBER;
        total_seconds# NUMBER;
        sql_name# VARCHAR2(50);
    BEGIN
        SELECT NAME, text INTO sql_name#, SQL_TEXT# FROM PIKALKA.d_sql WHERE project_id = 1 AND id = sql_id$;
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':guid', q(GUID#));
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':tin', TIN#);
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':dt1', q(DT1#));
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':dt2', q(DT2#));
        SQL_TEXT# := REPLACE(SQL_TEXT#, ':c_distr', C_DISTR#);

        BEGIN
            time_begin# := SYSTIMESTAMP;
            EXECUTE IMMEDIATE TO_CHAR(SQL_TEXT#);
            seconds# := ROUND(timestamp_diff(time_begin#, SYSTIMESTAMP), 3);
            total_seconds# := ROUND(timestamp_diff(START_TIME#, SYSTIMESTAMP), 3);
            protocol(seconds#, total_seconds#, sql_name#);
            IF DEBUG$ THEN
                msg('Ok! ' || sql_name#);
            END IF;
        EXCEPTION
            WHEN OTHERS THEN
                protocol(seconds#, total_seconds#, sql_name#, 1);
                IF DEBUG$ THEN
                   msg('Error! '|| sql_name# || ' ' || SQLERRM);
                ELSE
                   error_to_log(sql_name# || ' ' || SQLERRM);
                END IF;
        END;
    END;

    PROCEDURE session_start
    IS
    BEGIN
        INSERT INTO PIKALKA.pass_jrn (guid, dt1, dt2, tin, guid_user)
            VALUES (GUID#, DT1#, DT2#, TIN#, GUID_USER#);
        COMMIT;
    END;

    PROCEDURE session_end
    IS
        PRAGMA AUTONOMOUS_TRANSACTION;
        seconds# NUMBER;
    BEGIN
        -- визначаємо скільки часу тривала підготовка даних
        seconds# := ROUND(timestamp_diff(START_TIME#, SYSTIMESTAMP), 0);
        msg('Seconds - ' || TO_CHAR(seconds#));
        protocol(0, seconds#, 'Завершено');
        INSERT INTO PIKALKA.pass_log (guid, dt1, dt2, tin, guid_user, tm, prepared)
            VALUES (GUID#, DT1#, DT2#, TIN#, GUID_USER#, seconds#, 1);
        UPDATE PIKALKA.pass_jrn SET tm = seconds# WHERE guid = GUID#;
        DELETE FROM PIKALKA.pass_kontr_kredit_1 WHERE guid = GUID#;
        DELETE FROM PIKALKA.pass_kontr_kredit_2 WHERE guid = GUID#;
        DELETE FROM PIKALKA.pass_kontr_zobov_1 WHERE guid = GUID#;
        DELETE FROM PIKALKA.pass_kontr_zobov_2 WHERE guid = GUID#;
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
                    IF DEBUG$ THEN msg(sql# || chr(10) || SQLERRM); END IF;
            END;
        END LOOP;
        
        IF DEBUG$ THEN msg('TRUNCATE ALL TABLES'); END IF;
    END;
    
    PROCEDURE delete_old_data
    IS
        sql# VARCHAR2(500);
    BEGIN
        DELETE FROM PIKALKA.pass_jrn WHERE tin = TIN# AND dt1 = DT1# AND dt2 =DT2#;
        DELETE FROM PIKALKA.pass_steps WHERE guid IN 
            (SELECT GUID FROM pass_steps MINUS SELECT guid FROM PIKALKA.pass_jrn);
        
        FOR cur IN (SELECT tbl FROM PIKALKA.d_sql WHERE project_id = 1 ORDER BY id) LOOP
            sql# := 'DELETE FROM PIKALKA.' || cur.tbl || ' WHERE guid IN (SELECT GUID FROM pass_' || cur.tbl || ' MINUS SELECT guid FROM PIKALKA.pass_jrn)';
            BEGIN
                EXECUTE IMMEDIATE sql#;
            EXCEPTION
                WHEN OTHERS THEN
                    IF DEBUG$ THEN msg(sql# || chr(10) || SQLERRM); END IF;
            END;
        END LOOP;
        COMMIT;
        IF DEBUG$ THEN msg('DELETE FROM ALL TABLES BY guid BY PERIOD'); END IF;
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
                    IF DEBUG$ THEN msg(sql# || chr(10) || SQLERRM); END IF;
            END;
        END LOOP;
        
        COMMIT;
        IF DEBUG$ THEN msg('DELETE FROM ALL TABLES BY guid'); END IF;
    END;

    PROCEDURE prepare (tin$ NUMBER, dt1$ VARCHAR2, dt2$ VARCHAR2, guid_user$ VARCHAR2, guid$ VARCHAR2 DEFAULT '')
    IS 
        cnt# NUMBER;
    BEGIN
        -- засікаємо час початку процедури
        START_TIME# := SYSTIMESTAMP;
        STEP# := 0;

        TIN# := tin$;
        DT1# := dt1$;
        DT2# := dt2$;
        GUID_USER# := guid_user$;

        cnt# := check_started;
        IF cnt# > 0 THEN msg('The procudure PASSPORT.PREPARE works at this time.'); RETURN; END IF;

        -- видаляємо всі старі дані по вказаному коду за вказаний період
        --delete_old_data;

        GUID# := NVL(guid$, SYS_GUID());
        IF DEBUG$ THEN msg('GUID - ' || GUID#); END IF;
        
        -- шукаємо C_DISTR
        SELECT c_distr INTO C_DISTR# FROM (
            SELECT c_distr
            FROM RG02.r21taxpay r, U_2900Z.d_stan s
            WHERE tin = tin$ AND r.c_stan = s.c_stan(+)
            ORDER BY s.sort
        ) WHERE ROWNUM = 1;

        -- запис в журнал (який періодично очищається)
        session_start;
        protocol(0, 0, 'Початок');

        FOR id_cur IN (SELECT id FROM PIKALKA.d_sql WHERE project_id = 1 ORDER BY id) LOOP
            execute_sql(id_cur.id);
        END LOOP;

        -- запис в лог (не очищається ніколи)
        session_end;

        COMMIT;
    END;

    PROCEDURE create_job (tin$ NUMBER, dt1$ VARCHAR2, dt2$ VARCHAR2, guid_user$ VARCHAR2, guid$ VARCHAR2 DEFAULT NULL)
    IS
        count#  NUMBER;
        job_n#  NUMBER := 0;
        job#    VARCHAR2(2000);
        pref#   VARCHAR2(10) := '--GUID=';
    BEGIN
        BEGIN
            SELECT COUNT(*) INTO count# FROM user_jobs j
            WHERE SUBSTR(j.what, 8, INSTR(j.what, chr(10))-8) = guid_user$;
        EXCEPTION
            WHEN OTHERS THEN count# := 0;
        END;
        IF count# = 0 THEN
            job# := pref# || guid_user$ || chr(10) ||
            'BEGIN PIKALKA.passport.prepare(' || tin$ || ', ' || q(dt1$) || ', ' || q(dt2$) || ', ' || q(guid_user$) || ', ' || q(guid$) || '); END;';
            IF DEBUG$ THEN msg(job#); END IF;
            DBMS_JOB.submit(job_n#, job#);
            COMMIT;
        END IF;
    END;

END;
/
