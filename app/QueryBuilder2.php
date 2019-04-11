<?php

namespace App;

class QueryBuilder2
{
    protected $pdo;
    protected $tables;
    protected $fields;
    protected $wheres;
    protected $order;

    public function __construct (\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->fields = 'SELECT *';
    }

    public function table ($string)
    {
        $this->tables = 'FROM ' . $string;
        return $this;
    }

    public function select ($string = '*')
    {
        $this->fields = 'SELECT ' . $string;
        return $this;
    }

    public function where ($string)
    {
        $this->wheres = 'WHERE ' . $string;
        return $this;
    }

    public function orderBy ($string)
    {
        $this->order = 'ORDER BY ' . $string;
        return $this;
    }

    public function getSQL (): string
    {
        $cr = chr(13) . chr(10);
        $sql = $this->fields . $cr . $this->tables;
        if ($this->wheres !== null) {$sql .= $cr . $this->wheres;}
        if ($this->order !== null) {$sql .= $cr . $this->order;}

        return $sql;
    }

    /* Получить все записи */
    public function get ($data = []): array
    {
        $sql = $this->getSQL();
        $stmt = $this->execute($sql, $data);
        return $stmt->fetchAll();
    }

    /* Получить значение первого столпца первой строки */
    public function getValue ($field, $data = [])
    {
        $this->fields = 'SELECT ' . $field;
        $sql = $this->getSQL();
        $stmt = $this->execute($sql, $data);
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        return $row[0];
    }

    /* Получить первую строку */
    public function first ($data = []): array
    {
        $sql = $this->getSQL();
        $stmt = $this->execute($sql, $data);
        return $stmt->fetch();
    }

    /* Получить массив значений одного столбца */
    public function pluck ($field, $data = []): array
    {
        $this->fields = 'SELECT ' . $field;
        $sql = $this->getSQL();
        $stmt = $this->execute($sql, $data);
        $rows = $stmt->fetchAll(\PDO::FETCH_NUM);
        $result = [];
        foreach ($rows as $row) {
            $result[] = $row[0];
        }
        return $result;
    }

    protected function execute ($sql, array $data = [])
    {
        $stmt = null;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $stmt;
    }

}