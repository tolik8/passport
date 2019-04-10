<?php

namespace App\controllers;

use alhimik1986\PhpExcelTemplator\PhpExcelTemplator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Helper;

class PassToExcel extends DBController
{
    protected $role = '22'; // Роль 22 - Паспорт платника
    protected $ss; // SpreadSheet
    protected $guid;
    protected $c_distr;
    protected $default_params;
    protected $templateVars;
    protected $task;

    public function index (): void
    {
        $templateFile = $this->root . '/xls/passport/template.xlsx';
        $outputFile = './passport.xlsx';
        $outputMethod = true;
        $this->default_params = $this->templateVars = [];

        try {
            $this->ss = IOFactory::load($templateFile);
        } catch (\Exception $e) {
            echo $e->getMessage(); Exit;
        }

        try {
            for ($i = 0; $i <= $this->ss->getSheetCount()-1; $i++) {
                $this->templateVars[$i+1] = $this->ss->getSheet($i)->toArray();
                $this->default_params[$i+1] = $this->getDefaultParams($this->templateVars[$i+1]);
            }
        } catch (\Exception $e) {
            echo $e->getMessage(); Exit;
        }

        //$pattern = '#^[0-9a-zA-Z]{32}$#';
        //$this->pass_guid = Helper::RegEx($pattern, $_POST['guid'], 0);
        $this->guid = Helper::CheckRegEx('guid', $_POST['guid']);

        $params = $this->db->getOneRow('PIKALKA.pass_jrn', ['guid' => $this->guid]);
        //$guid_param = ['guid' => $this->pass_guid];

        $sql = getSQL('passport/get_tasks_guid.sql');
        $task = $this->db->getKeyValueFromSQL($sql, ['guid' => $this->guid]);

        // Реєстраційні дані
        if (isset($task[1])) {
            $this->toExcel_RegData(1, $params);
        }

        // Пов’язані
        $this->toExcel_Related(2, $task);

        // Контрагенти
        $this->toExcel_Kontr(3, $task);

        // Баланс
        if (isset($task[4])) {
            $array = $this->db->getAll('PIKALKA.pass_balance', ['guid' => $task[4]], 'period_year, period_month');
            $t_array = $this->transform($array);
            $this->setSheet(4, $t_array);
        }

        if (isset($task[5])) {
            // ПДВ
            $array1 = $this->db->getAll('PIKALKA.pass_pdv', ['guid' => $task[5]], 'period');
            $array1 = $this->addPrefix($array1, 'T1.');
            //$this->setSheet(7, $array);

            // ПДВ РІК
            $array2 = $this->db->getAll('PIKALKA.pass_pdv_rik', ['guid' => $task[5]], 'period_year');
            $array2 = $this->addPrefix($array2, 'T2.');
            $array = array_merge($array1, $array2);
            $this->setSheet(7, $array);
        }

        //PhpExcelTemplator::outputSpreadsheetToFile($this->ss, $outputFile);
        //exit;

        // Прибуток
        if (isset($task[6])) {
            $array = $this->db->getAll('PIKALKA.pass_pributok', ['guid' => $task[6]], 'period_year, period_month');
            $this->setSheet(4, $array);
        }

        // ЄСВ
        if (isset($task[7])) {
            $array = $this->db->getAll('PIKALKA.pass_esv', ['guid' => $task[7]], 'period');
            $this->setSheet(5, $array);
        }

        // Повідомлення
        if (isset($task[8])) {
            $array = $this->db->getAll('PIKALKA.pass_povidom', ['guid' => $task[8]], 'period_year');
            $this->setSheet(9, $array);
        }

        // Реєстр ПДВ
        $array = $this->db->getAll('AISR.pdv_act_r', ['tin' => $params['TIN']], 'dat_svd');
        $this->setSheet(10, $array);

        // Засновники
        $sql = 'SELECT SUM(sum_infund) FROM rg02.r21pfound WHERE tin = :tin';
        $sum_infund = $this->db->getOneValueFromSQL($sql, $params);
        $sql = getSQL('passport/founders.sql');
        $tmp_params = array_merge($params, ['sum_infund' => $sum_infund]);
        $array = $this->db->getAllFromSQL($sql, $tmp_params);
        $this->setSheet(11, $array);

        // запис в pass_log
        $sql_params = [
            'guid' => $this->guid, 'dt1' => $params['DT1'], 'dt2' => $params['DT2'],
            'tin' => $params['TIN'], 'guid_user' => $this->myUser->guid, 'tm' => 0,
        ];
        $this->db->insert('PIKALKA.pass_log', $sql_params);

        // Повідомлення (видалення листа)
//        if (!isset($task[8])) {
//            try {
//                $this->ss->removeSheetByIndex(8);
//            } catch (\Exception $e) {
//                echo $e->getMessage();
//            }
//        }

        if ($outputMethod) {PhpExcelTemplator::outputSpreadsheetToFile($this->ss, $outputFile);}
        else {PhpExcelTemplator::saveSpreadsheetToFile($this->ss, $outputFile);}
    }

    protected function toExcel_RegData ($index, $params): void
    {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $sheet = $this->ss->getSheet($index - 1);
        } catch (\Exception $e) {
            echo $e->getMessage(); Exit;
        }
        $reg_data = $this->excelRegData($params);
        $input_xlsParams = ['{tin}' => $params['TIN'], '{dt1}' => $params['DT1'], '{dt2}' => $params['DT2']];
        $data_from_oracle = array_merge($input_xlsParams, $reg_data);

        /** @noinspection PhpUndefinedMethodInspection */
        $templateCells = $sheet->toArray();
        $default_params = $this->getDefaultParams($templateCells);
        $templateParams = array_merge($default_params, $data_from_oracle);

        PhpExcelTemplator::renderWorksheet($sheet, $templateCells, $templateParams);
    }

    protected function toExcel_Related ($index, $task): void
    {
        if (!isset($task[$index])) {return;}

        $sql = 'SELECT t.*, \'\' blank FROM PIKALKA.pass_pov_t1 t WHERE guid = :guid ORDER BY c_distr, tin, c_stan';
        $array1 = $this->db->getAllFromSQL($sql, ['guid' => $task[2]]);
        $array1 = $this->transform($array1, 'T1.');

        $sql = 'SELECT c_distr, tin, ur_name, c_stan, post_name, typ, pin, NAME FROM pass_pov_t2 WHERE GUID = :GUID ORDER BY t DESC, tin, c_distr, c_stan, c_post';
        $array2 = $this->db->getAllFromSQL($sql, ['guid' => $task[2]]);
        $array2 = $this->transform($array2, 'T2.');

        $array3 = $this->db->getAll('PIKALKA.pass_pov_t3', ['guid' => $task[2]], 't DESC, tin, c_distr, c_stan, c_post');
        $array3 = $this->transform($array3, 'T3.');

        $array4 = $this->db->getAll('PIKALKA.pass_pov_t4', ['guid' => $task[2]], 't DESC, tin, c_distr, c_stan, c_post');
        $array4 = $this->transform($array4, 'T4.');

        $array5 = $this->db->getAll('PIKALKA.pass_pov_t5', ['guid' => $task[2]], 't DESC, tin, c_distr, c_stan');
        $array5 = $this->transform($array5, 'T5.');

        $data_from_oracle = array_merge($array1, $array2, $array3, $array4, $array5);

        $this->setSheet($index, $data_from_oracle);
    }

    protected function toExcel_Kontr ($index, $task): void
    {
        if (!isset($task[$index])) {return;}

        $sql = 'SELECT ROWNUM n, t.* FROM (SELECT * FROM PIKALKA.pass_kontr_kredit_3 WHERE guid = :guid ORDER BY obs DESC, tin) t';
        $array1 = $this->db->getAllFromSQL($sql, ['guid' => $task[$index]]);
        $t_array = $this->transform($array1, 'T1.');
        $t_array = $this->addFieldPercent($t_array, 'T1.OBS#', 'T1.PERCENT#');
        $sum1 = $this->getSumFromArray($t_array, 'T1.OBS');
        $sum2 = $this->getSumFromArray($t_array, 'T1.PDV');
        $data_from_oracle1 = array_merge($t_array, $sum1, $sum2);

        $sql = 'SELECT ROWNUM n, t.* FROM (SELECT * FROM PIKALKA.pass_kontr_zobov_3 WHERE guid = :guid ORDER BY obs DESC, cp_tin) t';
        $array2 = $this->db->getAllFromSQL($sql, ['guid' => $task[$index]]);
        $t_array = $this->transform($array2, 'T2.');
        $t_array = $this->addFieldPercent($t_array, 'T2.OBS#', 'T2.PERCENT#');
        $sum1 = $this->getSumFromArray($t_array, 'T2.OBS');
        $sum2 = $this->getSumFromArray($t_array, 'T2.PDV');
        $data_from_oracle2 = array_merge($t_array, $sum1, $sum2);

        $data_from_oracle = array_merge($data_from_oracle1, $data_from_oracle2);

        $this->setSheet($index, $data_from_oracle);
    }

    /* $t_array = $this->addFieldPercent($t_array, 'T2.OBS#', 'T2.PERCENT#'); */
    protected function addFieldPercent (array $array, $scan, $new_field, $precision = 0): array
    {
        $sum = array_sum($array[$scan]);
        $new_array[$new_field] = [];
        foreach ($array[$scan] as $item) {
            $new_array[$new_field][] = round($item / $sum * 100, $precision);
        }
        return array_merge($array, $new_array);
    }

    protected function addPrefix (array $array, $prefix): array
    {
        $result = [];
        foreach ($array as $row) {
            $new_row = [];
            foreach ($row as $key => $item) {
                $new_row[$prefix.$key] = $item;
            }
            $result[] = $new_row;
        }
        return $result;
    }

    protected function excelRegData ($input_params): array
    {
        $tin = $params['TIN'] = $input_params['TIN'];

        $sql = 'SELECT PIKALKA.tax.get_dpi_by_tin(:tin) FROM dual';
        $dpi = $this->c_distr = $this->db->getOneValueFromSQL($sql, $params);
        $type_pl = (int) $this->db->getOneValue('FACE_MODE','RG02.r21taxpay', ['tin' => $tin, 'c_distr' => $dpi]);

        $r21taxpay = $this->db->getOneRow('RG02.r21taxpay', ['tin' => $tin, 'c_distr' => $dpi]);

        $stan_name = $this->db->getOneValue('N_STAN','ETALON.E_S_STAN', ['c_stan' => $r21taxpay['C_STAN']]);
        $kved_name = $this->db->getOneValue('NU','ETALON.E_KVED', ['kod' => $r21taxpay['KVED']]);

        $sql = getSQL('passport/get_address.sql');
        $address = $this->db->getOneValueFromSQL($sql, ['tin' => $tin, 'c_distr' => $dpi]);

        if ($type_pl === 1) {
            $sql = 'SELECT c_post, pin, name, n_tel FROM RG02.r21manager WHERE tin = :tin';
            $r21manager = $this->db->getKeyValuesFromSQL($sql, $params);
            $reg_params_ur = [
                '{r21manager.dir}' => Helper::utf8($r21manager[1]['NAME']),
                '{r21manager.buh}' => Helper::utf8($r21manager[2]['NAME']),
                '{r21manager.dir_tel}' => Helper::utf8($r21manager[1]['N_TEL']),
                '{r21manager.buh_tel}' => Helper::utf8($r21manager[2]['N_TEL']),
            ];
        } else {
            $reg_params_ur = [];
        }

        $sql = getSQL('passport/get_r21stan_h.sql');
        $array = $this->db->getAllFromSQL($sql, ['tin' => $tin, 'c_distr' => $dpi]);
        //$array = $this->transform1($array);
        $stan_h = $this->transform($array, 'SH.');

        $reg_params = [
            '{r21taxpay.c_distr}' => $this->c_distr,
            '{r21taxpay.name}' => Helper::utf8($r21taxpay['NAME']),
            '{r21taxpay.stan}' => $r21taxpay['C_STAN'],
            '{r21taxpay.stan_name}' => Helper::utf8($stan_name),
            '{r21taxpay.kved}' => $r21taxpay['KVED'],
            '{r21taxpay.kved_name}' => Helper::utf8($kved_name),
            '{r21taxpay.d_reg_sti}' => $r21taxpay['D_REG_STI'],
            '{r21paddr.address}' => Helper::utf8($address),
        ];

        $sql = 'SELECT * FROM AISR.pdv_act_r WHERE tin = :tin AND dat_anul IS NULL AND ROWNUM = 1';
        $pdv_act_r = $this->db->getOneRowFromSQL($sql, $params);
        if (!empty($pdv_act_r)) {$reg_params['{pdv_act_r.dat_reestr}'] = $pdv_act_r['DAT_REESTR'];}

        return array_merge($reg_params, $reg_params_ur, $stan_h);
    }

    protected function getDefaultParams ($params): array
    {
        $pattern = '@(\{[0-9a-zA-Z_.]+?\})|(\[\[[0-9a-zA-Z_.]+?#\]\])|(\[[0-9a-zA-Z_.]+?#\])@';
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

    /* $sum = $this->getSumFromArray($t_array, 'T1.PDV'); */
    protected function getSumFromArray (array $array, $find): array
    {
        $find_array = explode('.', $find);
        $prefix = $find_array[0] . '.';
        $field = $find_array[1];
        $sum_name = '{' . $prefix . $field . '_SUM}';
        $sum = array_sum($array[$prefix . $field . '#']);
        return [$sum_name => $sum];
    }

    protected function setSheet ($index, $array): void
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

    protected function transform (array $array, $prefix = ''): array
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