<?php

namespace App;

class QueryBuilder
{
    protected $pdo;
    protected $log;
    protected $errors_before_transaction;
    public $fields = '';
    public $where = '';
    public $sql_time = 0;
    public $sql_count = 0;
    public $errors_count = 0;
    public $columns = [];
    public $last_result;

    public function __construct (\PDO $pdo, \App\Logger $Logger)
    {
        $this->pdo = $pdo;
        $this->log = $Logger;
    }

    protected function execute ($sql, array $data = [])
    {
        $stmt = null;

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }

        return $stmt;
    }

    protected function ParametersString (array $data)
    {
        $string = '';
        $keys = array_keys($data);

        foreach ($keys as $key) $string .= $key . ' = :' . $key . ' AND ';
        $string = rtrim($string, ' AND ');

        return $string;
    }

    public function getAll ($table, array $data = [], $sort = '')
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $sql = "SELECT * FROM {$table}";
        $string = $this->ParametersString($data);
        if (!empty($data)) $sql .= ' WHERE ' . $string;
        if ($sort != '') {$sql .= ' ORDER BY ' . $sort;}

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }
        if (isset($stmt)) $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getFromBuild ($table, array $data = [], $sort = '')
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        if ($this->fields == '') $fields = '*'; else $fields = $this->fields;
        $sql = 'SELECT '.$fields.chr(10).'FROM '.$table;
        if ($this->where != '') $sql .= chr(10).'WHERE ' . $this->where;
        if ($sort != '') {$sql .= chr(10).'ORDER BY ' . $sort;}

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }
        if (isset($stmt)) $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);]*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getAllFromSQL ($sql, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        //$result = $this->execute($sql, $data);

        if (!empty($result)) $this->columns = array_keys($result[0]); else $this->columns = [];

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getOneValue ($column, $table, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $sql = "SELECT {$column} FROM {$table}";
        $string = $this->ParametersString($data);
        if (!empty($data)) $sql .= ' WHERE ' . $string;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $result = $row[0];

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getOneValueFromSQL ($sql, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $result = $row[0];

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getOneValueFromBuild ($table, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        if ($this->fields == '') $fields = '*'; else $fields = $this->fields;
        $sql = 'SELECT '.$fields.chr(10).'FROM '.$table;
        if ($this->where != '') $sql .= chr(10).'WHERE ' . $this->where;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $result = $row[0];

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getOneRow ($table, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $sql = "SELECT * FROM {$table}";
        $string = $this->ParametersString($data);
        if (!empty($data)) $sql .= ' WHERE ' . $string;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getOneRowFromSQL ($sql, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getOneRowFromBuild ($table, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        if ($this->fields == '') $fields = '*'; else $fields = $this->fields;
        $sql = 'SELECT '.$fields.chr(10).'FROM '.$table;
        if ($this->where != '') $sql .= chr(10).'WHERE ' . $this->where;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getOneCol ($column, $table, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $sql = "SELECT {$column} FROM {$table}";
        $string = $this->ParametersString($data);
        if (!empty($data)) $sql .= ' WHERE ' . $string;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            foreach ($row as $value) $result[] = $value;
        }

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getOneColFromSQL ($sql, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            foreach ($row as $value) $result[] = $value;
        }

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    // Результат массив в котором первый столбец это ключ, а второй значение
    public function getKeyValue ($columns, $table, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $sql = "SELECT {$columns} FROM {$table}";
        $string = $this->ParametersString($data);
        if (!empty($data)) $sql .= ' WHERE ' . $string;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $rows = $stmt->fetchAll(\PDO::FETCH_NUM);
        foreach ($rows as $row) $result[$row[0]] = $row[1];

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    // Результат массив в котором первый столбец это ключ, а стальные ассоциативный массив
    public function getKeyValues ($columns, $table, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $sql = "SELECT {$columns} FROM {$table}";
        $string = $this->ParametersString($data);
        if (!empty($data)) $sql .= ' WHERE ' . $string;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);

        $rows2 = $fields = [];
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows[0] as $key => $value) $fields[] = $key;
        foreach ($rows as $row) $rows2[] = array_values($row);
        $col_count = $stmt->columnCount();
        foreach ($rows2 as $row) {
            for ($i = 2; $i <= $col_count; $i++) {
                $column_name = $fields[$i-1];
                $result[$row[0]][$column_name] = $row[$i-1];
            }
        }


        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getKeyValuesFromSQL ($sql, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $rows2 = $fields = [];
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows[0] as $key => $value) $fields[] = $key;
        foreach ($rows as $row) $rows2[] = array_values($row);
        $col_count = $stmt->columnCount();
        foreach ($rows2 as $row) {
            for ($i = 2; $i <= $col_count; $i++) {
                $column_name = $fields[$i-1];
                $result[$row[0]][$column_name] = $row[$i-1];
            }
        }

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getKeyValuesFromBuild ($table, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        if ($this->fields == '') $fields = '*'; else $fields = $this->fields;
        $sql = 'SELECT '.$fields.chr(10).'FROM '.$table;
        if ($this->where != '') $sql .= chr(10).'WHERE ' . $this->where;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $rows2 = $fields = [];
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows[0] as $key => $value) $fields[] = $key;
        foreach ($rows as $row) $rows2[] = array_values($row);
        $col_count = $stmt->columnCount();
        foreach ($rows2 as $row) {
            for ($i = 2; $i <= $col_count; $i++) {
                $column_name = $fields[$i-1];
                $result[$row[0]][$column_name] = $row[$i-1];
            }
        }

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function insert ($table, array $data)
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $keys = implode(', ', array_keys($data));
        $tags = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$keys}) VALUES ({$tags})";

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }
        if (isset($stmt)) $result = $stmt->rowCount();*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->rowCount();

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function insertFromSQL ($sql, array $data)
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }
        if (isset($stmt)) $result = $stmt->rowCount();*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->rowCount();

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function update ($table, array $update, array $where = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $keys = array_keys($update);
        $string = '';
        foreach ($keys as $key) $string .= $key . ' = :' . $key . ', ';
        $keys = rtrim($string, ', ');
        $data = array_merge($update, $where);

        $where_string = $this->ParametersString($where);
        $sql = "UPDATE {$table} SET {$keys} WHERE {$where_string}";

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }
        if (isset($stmt)) $result = $stmt->rowCount();*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->rowCount();

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function delete ($table, array $data)
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $string = $this->ParametersString($data);
        $sql = "DELETE FROM {$table} WHERE {$string}";

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }
        if (isset($stmt)) $result = $stmt->rowCount();*/
        $stmt = $this->execute($sql, $data);
        $result = $stmt->rowCount();

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getCount ($table, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $sql = "SELECT COUNT(*) cnt FROM {$table}";
        $string = $this->ParametersString($data);
        if (!empty($data)) $sql .= ' WHERE ' . $string;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $stmt = $this->execute($sql, $data);
        $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
        $result = $rows['CNT'];

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function runSQL ($sql, array $data = [])
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        /*try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {$stmt->bindValue(':' . $key, $value);}
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql, $data]);
        }*/
        $this->execute($sql, $data);

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function getNewGUID ()
    {
        $start_time = microtime(true);
        $result = null;
        $this->sql_time = 0;
        $this->sql_count++;
        $this->last_result = true;

        $sql = 'SELECT sys_guid() FROM dual';

        /*try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } catch (\Exception $e) {
            $this->last_result = false;
            $this->errors_count++;
            $this->log->save(debug_backtrace(), [$e->getMessage(), $sql]);
        }*/
        $stmt = $this->execute($sql);
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $result = $row[0];

        if (!isset($e)) $this->sql_time = round(microtime(true) - $start_time, 4);

        return $result;
    }

    public function beginTransaction ()
    {
        $this->errors_before_transaction = $this->errors_count;
        $this->pdo->beginTransaction();
    }

    public function endTransaction ()
    {
        if ($this->errors_before_transaction == $this->errors_count)
            $this->pdo->commit(); else $this->pdo->rollback();
    }

    public function commit ()
    {
        $this->pdo->commit();
    }

    public function rollback ()
    {
        $this->pdo->rollback();
    }

}