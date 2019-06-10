-- passport/tasks_portable.sql
SELECT t.*
FROM PIKALKA.d_pass_task t
WHERE INSTR(',' || :task || ',', ',' || t.id || ',') > 0
ORDER BY t.id