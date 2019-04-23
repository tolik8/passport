<?php

namespace App\controllers;

use alhimik1986\PhpExcelTemplator\PhpExcelTemplator;
/*use PhpOffice\PhpSpreadsheet\Exception;*/
use PhpOffice\PhpSpreadsheet\IOFactory;

class TestExcel extends DBController
{
    protected $need_access = false;
    protected $ss;

    public function index(): void
    {
        echo '<a href="/test/export">Export to Excel</a>';
    }

    public function export(): void
    {
        $templateFile = $this->root . '/xls/test.xlsx';
        $outputFile = './test.xlsx';

        try {
            $this->ss = IOFactory::load($templateFile);
        } catch (\Exception $e) {
            echo $e->getMessage(); Exit;
        }
        $data1 = ['{N}' => 123, '{NAME}' => 'HELLO'];

        $sql = 'SELECT c_distr, tin, name, c_stan, kved FROM RG02.r21taxpay WHERE c_stan NOT IN (17,27) AND tin IN (300400, 24630349)';
        $array1 = $this->db->selectRaw($sql)->get();
        $t_array1 = $this->transform($array1, 'T1.');
        $sum1 = $this->getSumFromArray($t_array1, 'T1.C_STAN');

        $sql = 'SELECT * FROM PIKALKA.d_pass_task WHERE ID < 5';
        $array2 = $this->db->selectRaw($sql)->get();
        $t_array2 = $this->transform($array2, 'T2.');
        $sum2 = $this->getSumFromArray($t_array2, 'T2.ID');

        $data_array = array_merge($t_array1, $t_array2, $sum1, $sum2, $data1);

        // Вставка інформації в Лист 1
        $this->setSheet(1, $data_array);

        $sql = 'SELECT id as n, name, type_id FROM PIKALKA.d_enter ORDER BY ID';
        $array = $this->db->selectRaw($sql)->get();
        $t_array = $this->transform($array, 'T1.');
        $sum = $this->getSumFromArray($t_array, 'T1.TYPE_ID');

        $data_array = array_merge($t_array, $sum);

        // Вставка інформації в Лист 2
        $this->setSheet(2, $data_array);

        PhpExcelTemplator::outputSpreadsheetToFile($this->ss, $outputFile);
    }

    protected function getDefaultParams($params): array
    {
        /* Шаблон для вибору з листа ексель комірок {data} [data] [[data]] */
        $pattern = '#(\{[0-9a-zA-Z_.]+?\})|(\[\[[0-9a-zA-Z_.]+?\]\])|(\[[0-9a-zA-Z_.]+?\])#';
        $result = $new_array = [];

        foreach ($params as $param) {
            foreach ($param as $item) {
                if ($item !== null) {
                    preg_match_all($pattern, $item, $matches);
                    foreach ($matches[0] as $match) {$new_array[] = $match;}
                }
            }
        }
        foreach ($new_array as $item) {$result[$item] = '';}

        return $result;
    }

    protected function getSumFromArray(array $array, $find): array
    {
        $find_array = explode('.', $find);
        $prefix = $find_array[0] . '.';
        $field = $find_array[1];
        $sum_name = '{' . $prefix . $field . '_SUM}';
        $sum = array_sum($array[$prefix . $field . '#']);
        return [$sum_name => $sum];
    }

    protected function setSheet($index, $array): void
    {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $sheet = $this->ss->getSheet($index - 1);
            /** @noinspection PhpUndefinedMethodInspection */
            $templateCells = $sheet->toArray();
            $default_params = $this->getDefaultParams($templateCells);
            $templateParams = array_merge($default_params, $array);
            PhpExcelTemplator::renderWorksheet($sheet, $templateCells, $templateParams);
        } catch (\Exception $e) {
            echo $e->getMessage(); Exit;
        }
    }

    protected function transform(array $array, $prefix = ''): array
    {
        $result = [];
        if (empty($array)) {return $result;}
        $columns = array_keys($array[0]);

        foreach ($array as $row) {
            foreach ($columns as $col) {
                $value_utf8 = mb_convert_encoding($row[$col], 'utf-8', 'windows-1251');
                $result[$prefix . $col . '#'][] = $value_utf8;
            }
        }
        return $result;
    }

}
