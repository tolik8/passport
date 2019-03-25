<?php

namespace App;

interface QueryBuilderInterface
{
    public function getAll ($tables, array $data = [], $sort = ''): array;

    public function getAllFromSQL ($sql, array $data = []): array;

    public function getOneValue ($field, $table, array $data = []);

    public function getOneValueFromSQL ($sql, array $data = []);

    public function getOneRow ($table, array $data = []): array;

    public function getOneRowFromSQL ($sql, array $data = []): array;

    public function getOneCol ($fields, $tables, array $data = []): array;

    public function getOneColFromSQL ($sql, array $data = []): array;

    public function getKeyValue ($fields, $tables, array $data = [], $sort = ''): array;

    public function getKeyValueFromSQL ($sql, array $data = []): array;

    public function getKeyValues ($fields, $tables, array $data = []): array;

    public function getKeyValuesFromSQL ($sql, array $data = []): array;

    public function insert ($table, array $data): int;

    public function update ($table, array $update, array $where = []): int;

    public function delete ($table, array $data): int;

    public function getCount ($table, array $data = []): int;

    public function runSQL ($sql, array $data = []): int;

    public function getNewGUID (): string;

    public function beginTransaction (): void;

    public function endTransaction (): void;

    public function commit (): void;

    public function rollback (): void;
}