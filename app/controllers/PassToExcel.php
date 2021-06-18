<?php

namespace App\controllers;

use alhimik1986\PhpExcelTemplator\PhpExcelTemplator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Helper;
use App\Config;

class PassToExcel extends DBController
{
    protected $role = '22'; // Роль 22 - Паспорт платника
    protected $ss; // SpreadSheet
    protected $guid;
    protected $c_distr;
    protected $default_params;
    protected $templateVars;
    protected $task;

    public function index(): void
    {
        $db =$this->db;
        
        /* Отримати всі POST параметри */
        $this->guid = Helper::checkRegEx('guid', $_POST['guid']);

        $portable_mode = Config::get('settings.PORTABLE_MODE');

        if ($this->guid === null) {
            $params = [
                'TIN' => Helper::checkRegEx('tin', $_POST['tin']),
                'DT1' => Helper::checkRegEx('date', $_POST['dt1']),
                'DT2' => Helper::checkRegEx('date', $_POST['dt2']),
                'TASKS' => Helper::checkRegEx('list', $_POST['task']),
                'GUID_USER' => $this->myUser->guid,
            ];
            if ($portable_mode) {
                $sql = getSQL('passport\get_task_ready_guid_portable.sql');
            } else {
                $sql = getSQL('passport\get_task_ready_guid.sql');
            }
            $task = $db->selectRaw($sql, $params)->pluck('TASK_ID', 'GUID');
        } else {
            $this->guid = Helper::checkRegEx('guid', $_POST['guid']);
            $params = $db->table('pass_jrn')
                ->where('guid = :guid')->bind(['guid' => $this->guid])->first();

            $sql = getSQL('passport/get_tasks_guid.sql');
            $task = $db->selectRaw($sql, ['guid' => $this->guid])->pluck('TASK_ID', 'GUID');
        }

        /* Параметри Excel */
        $outputMethod = true;
        $templateFile = $this->root . '/xls/passport/template.xlsx';
        $outputFile = './passport.xlsx';
        $this->default_params = $this->templateVars = [];

        /* Створити ss - SpreadSheets */
        try {
            $this->ss = IOFactory::load($templateFile);
        } catch (\Exception $e) {
            echo $e->getMessage(); Exit;
        }

        /* Реєстраційні дані */
        if (isset($task[1])) {
            $this->toExcel_RegData(1, $params);
        }

        /* Пов’язані */
        $this->toExcel_Related(2, $task);

        /* Контрагенти */
        $this->toExcel_Kontr(3, $task);

        /* Баланс */
        if (isset($task[4])) {
            $array = $db->table('pass_balance')->where('guid = :guid')
                ->orderBy('period_year, period_month')->bind(['guid' => $task[4]])->get();
            $this->setSheet(4, $this->transform($array));
        }

        if (isset($task[5])) {
            /* ПДВ */
            $array1 = $db->table('pass_pdv')->where('guid = :guid')
                ->orderBy('period')->bind(['guid' => $task[5]])->get();
            $array1 = $this->addPrefix($array1, 'T1.');
            $t_array1 = $this->transform($array1);

            /* ПДВ РІК */
            $array2 = $db->table('pass_pdv_rik')->where('guid = :guid')
                ->orderBy('period_year')->bind(['guid' => $task[5]])->get();
            $array2 = $this->addPrefix($array2, 'T2.');
            $t_array2 = $this->transform($array2);

            $this->setSheet(5, array_merge($t_array1, $t_array2));
        }

        /* Прибуток */
        if (isset($task[6])) {
            $array = $db->table('pass_pributok')->where('guid = :guid')
                ->orderBy('period_year, period_month')->bind(['guid' => $task[6]])->get();
            $this->setSheet(6, $this->transform($array));
        }

        /* ЄСВ */
        if (isset($task[7])) {
            $array = $db->table('pass_esv')->where('guid = :guid')
                ->orderBy('period')->bind(['guid' => $task[7]])->get();
            $this->setSheet(7, $this->transform($array));
        }

        /* Повідомлення */
        if (isset($task[8])) {
            $array = $db->table('pass_povidom')->where('guid = :guid')
                ->orderBy('period_year')->bind(['guid' => $task[8]])->get();
            $this->setSheet(8, $this->transform($array));
        }

        /* 1-ДФ */
        if (isset($task[9])) {
            $sql = getSQL('passport/get_1df.sql');
            $array1 = $db->selectRaw($sql, ['kod' => $params['TIN']])->get();
            $array1 = $this->addPrefix($array1, 'T1.');
            $t_array1 = $this->transform($array1);

            $sql = getSQL('passport/get_1df_detail.sql');
            $array2 = $db->selectRaw($sql, ['tin' => $params['TIN']])->get();
            $array2 = $this->addPrefix($array2, 'T2.');
            $t_array2 = $this->transform($array2);

            $this->setSheet(9, array_merge($t_array1, $t_array2));
        }

        /* Площа */
        if (isset($task[10])) {

            $array1 = $db->table('pass_area_zag')->where('guid = :guid')
                ->orderBy('period_year, c_sti, koatuu, d_get')->bind(['guid' => $task[10]])->get();
            $array1 = $this->addPrefix($array1, 'T1.');
            $t_array1 = $this->transform($array1);

            $sql = getSQL('passport/area_group_year.sql');
            $array2 = $db->selectRaw($sql, ['guid' => $task[10]])->get();
            $array2 = $this->addPrefix($array2, 'T2.');
            $t_array2 = $this->transform($array2);

            $sql = getSQL('passport/area_group_year_dpi.sql');
            $array3 = $db->selectRaw($sql, ['guid' => $task[10]])->get();
            $array3 = $this->addPrefix($array3, 'T3.');
            $t_array3 = $this->transform($array3);

            $array4 = $db->table('pass_area')->where('guid = :guid')
                ->orderBy('period_year, c_sti, koatuu, d_get')->bind(['guid' => $task[10]])->get();
            $array4 = $this->addPrefix($array4, 'T4.');
            $t_array4 = $this->transform($array4);

            $this->setSheet(10, array_merge($t_array1, $t_array2, $t_array3, $t_array4));
        }

        /* Сплата і борг */
        if (isset($task[11])) {
            $array1 = $db->table('vw_pass_splata_dates')->pluck('N', 'DT');
            $t_array1 = $t_array2 = $t_array3 = [];
            foreach ($array1 as $key => $item) {
                $t_array1['{DT'.$key.'}'] = $item;
            }
            $array2 = $db->table('pass_splata2')
                ->where('guid = :guid')->orderBy('n')->bind(['guid' => $task[11]])->get();
            $exclude = ['GUID', 'N', 'RDATA', 'KOD'];
            foreach ($array2 as $record) {
                foreach ($record as $key => $value) {
                    if (!in_array($key, $exclude, true)) {
                        $t_array2['{' . $key . $record['N'] . '}'] = $value;
                    }
                }
            }
            $array3 = $db->table('pass_splata_esv')
                ->where('guid = :guid')->orderBy('n')->bind(['guid' => $task[11]])->get();
            foreach ($array3 as $record) {
                foreach ($record as $key => $value) {
                    $t_array3['{' . $key . $record['N'] . '}'] = $value;
                }
            }
            $this->setSheet(11, array_merge($t_array1, $t_array2, $t_array3));
        }

        /* Єдиний (фіз.) */
        if (isset($task[12])) {
            $array = $db->table('pass_edin')->where('guid = :guid')
                ->orderBy('period_year, period_month')->bind(['guid' => $task[12]])->get();
            $this->setSheet(12, $this->transform($array));
        }

        /* Контрагенти - зобов’язання (розріз) */
        if (isset($task[13])) {
            $array = $db->table('pass_nom_z2')->where('guid = :guid')
                ->orderBy('kod_tovaru, cp_tin')->bind(['guid' => $task[13]])->get();
            $this->setSheet(13, $this->transform($array));
        }

        /* Контрагенти - кредит (розріз) */
        if (isset($task[14])) {
            $array = $db->table('pass_nom_k2')->where('guid = :guid')
                ->orderBy('kod_tovaru, tin')->bind(['guid' => $task[14]])->get();
            $this->setSheet(14, $this->transform($array));
        }

        /* Ліцензії */
        if (isset($task[15])) {
            $sql = getSQL('passport/get_lic.sql');
            $array1 = $db->selectRaw($sql, ['tin' => $params['TIN']])->get();
            $array1 = $this->addPrefix($array1, 'T1.');
            $t_array1 = $this->transform($array1);

            $array2 = $db->table('TOLIK.pass_lic_rtp')->where('tin = :tin')
                ->orderBy('d_begin')->bind(['tin' => $params['TIN']])->get();
            $array2 = $this->addPrefix($array2, 'T2.');
            $t_array2 = $this->transform($array2);

            $array3 = $db->table('TOLIK.pass_lic_zp')->where('tin = :tin')
                ->orderBy('d_begin')->bind(['tin' => $params['TIN']])->get();
            $array3 = $this->addPrefix($array3, 'T3.');
            $t_array3 = $this->transform($array3);

            $this->setSheet(15, array_merge($t_array1, $t_array2, $t_array3));
        }

        /* Запис в pass_log */
        $params['TM'] = 0;
        $params['IP'] = $_SERVER['REMOTE_ADDR'];
        unset($params['DT0']);
        $db->table('pass_log')->insert($params);

        /* Видалення лишніх листів */
        for ($i = $this->ss->getSheetCount(); $i > 0; $i--) {
            if (!isset($task[$i])) {
                try {
                    $this->ss->removeSheetByIndex($i - 1);
                } catch (\Exception $e) {
                    //echo $e->getMessage();
                }
            }
        }

        if ($outputMethod) {
            PhpExcelTemplator::outputSpreadsheetToFile($this->ss, $outputFile);
        } else {
            PhpExcelTemplator::saveSpreadsheetToFile($this->ss, $outputFile);
        }

    }

    protected function toExcel_RegData($index, $params): void
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

    protected function toExcel_Related($index, $task): void
    {
        if (!isset($task[$index])) {return;}

        $sql = 'SELECT t.*, \'\' blank FROM pass_pov_t1 t WHERE guid = :guid ORDER BY c_distr, tin, c_stan';
        $array1 = $this->db->selectRaw($sql, ['guid' => $task[2]])->get();
        $array1 = $this->transform($array1, 'T1.');

        $sql = 'SELECT c_distr, tin, ur_name, c_stan, post_name, typ, pin, NAME FROM pass_pov_t2 WHERE GUID = :GUID ORDER BY t DESC, tin, c_distr, c_stan, c_post';
        $array2 = $this->db->selectRaw($sql, ['guid' => $task[2]])->get();
        $array2 = $this->transform($array2, 'T2.');

        $array3 = $this->db->table('pass_pov_t3')->where('guid = :guid')
            ->orderBy('t DESC, tin, c_distr, c_stan, c_post')->bind(['guid' => $task[2]])->get();
        $array3 = $this->transform($array3, 'T3.');

        $array4 = $this->db->table('pass_pov_t4')->where('guid = :guid')
            ->orderBy('t DESC, tin, c_distr, c_stan, c_post')->bind(['guid' => $task[2]])->get();
        $array4 = $this->transform($array4, 'T4.');

        $array5 = $this->db->table('pass_pov_t5')->where('guid = :guid')
            ->orderBy('t DESC, tin, c_distr, c_stan')->bind(['guid' => $task[2]])->get();
        $array5 = $this->transform($array5, 'T5.');

        $data_from_oracle = array_merge($array1, $array2, $array3, $array4, $array5);

        $this->setSheet($index, $data_from_oracle);
    }

    protected function toExcel_Kontr($index, $task): void
    {
        if (!isset($task[$index])) {return;}

        $sql = 'SELECT ROWNUM n, t.* FROM (SELECT * FROM pass_kontr_kredit_3 WHERE guid = :guid ORDER BY obs DESC, tin) t';
        $array1 = $this->db->selectRaw($sql, ['guid' => $task[$index]])->get();
        $t_array = $this->transform($array1, 'T1.');
        $t_array = $this->addFieldPercent($t_array, '#T1.OBS#', '#T1.PERCENT#');
        $sum1 = $this->getSumFromArray($t_array, 'T1.OBS');
        $sum2 = $this->getSumFromArray($t_array, 'T1.PDV');
        $data_from_oracle1 = array_merge($t_array, $sum1, $sum2);

        $sql = 'SELECT ROWNUM n, t.* FROM (SELECT * FROM pass_kontr_zobov_3 WHERE guid = :guid ORDER BY obs DESC, cp_tin) t';
        $array2 = $this->db->selectRaw($sql, ['guid' => $task[$index]])->get();
        $t_array = $this->transform($array2, 'T2.');
        $t_array = $this->addFieldPercent($t_array, '#T2.OBS#', '#T2.PERCENT#');
        $sum1 = $this->getSumFromArray($t_array, 'T2.OBS');
        $sum2 = $this->getSumFromArray($t_array, 'T2.PDV');
        $data_from_oracle2 = array_merge($t_array, $sum1, $sum2);

        $data_from_oracle = array_merge($data_from_oracle1, $data_from_oracle2);

        $this->setSheet($index, $data_from_oracle);
    }

    /* $t_array = $this->addFieldPercent($t_array, '#T1.OBS#', '#T1.PERCENT#'); */
    protected function addFieldPercent(array $array, $scan, $new_field, $precision = 0): array
    {
        if (empty($array)) {return [];}
        $sum = array_sum($array[$scan]);
        $new_array[$new_field] = [];
        foreach ($array[$scan] as $item) {
            $new_array[$new_field][] = round($item / $sum * 100, $precision);
        }
        return array_merge($array, $new_array);
    }

    protected function addPrefix(array $array, $prefix): array
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

    protected function excelRegData($input_params): array
    {
        $tin = $params['TIN'] = $input_params['TIN'];

        $sql = 'SELECT TOLIK.tax.get_dpi_by_tin(:tin) FROM dual';
        $dpi = $this->c_distr = $this->db->selectRaw($sql, $params)->getCell();
        $data = ['tin' => $tin, 'c_distr' => $dpi];
        $type_pl = (int) $this->db->table('RG02.r21taxpay')
            ->where('tin = :tin AND c_distr = :c_distr')->bind($data)->getCell('FACE_MODE');

        $data = ['tin' => $tin, 'c_distr' => $dpi];
        $r21taxpay = $this->db->table('RG02.r21taxpay')
            ->where('tin = :tin AND c_distr = :c_distr')->bind($data)->first();

        $data = ['c_stan' => $r21taxpay['C_STAN']];
        $stan_name = $this->db->table('ETALON.E_S_STAN')
            ->where('c_stan = :c_stan')->bind($data)->getCell('N_STAN');

        $data = ['kod' => $r21taxpay['KVED']];
        $kved_name = $this->db->table('ETALON.E_KVED')
             ->where('kod = :kod')->bind($data)->getCell('NU');

        $sql = getSQL('passport/get_address.sql');
        $address = $this->db->selectRaw($sql, ['tin' => $tin, 'c_distr' => $dpi])->getCell();

        $reg_params_ur = [];

        if ($type_pl === 1) {
            $sql = 'SELECT c_post, pin, name, n_tel FROM RG02.r21manager WHERE tin = :tin';
            $array = $this->db->selectRaw($sql, $params)->get();
            $r21manager = Helper::array_combine2($array);
            if (isset($r21manager[1])) {
                $reg_params_ur = [
                    '{r21manager.dir_pin}' => Helper::utf8($r21manager[1]['PIN']),
                    '{r21manager.dir}' => Helper::utf8($r21manager[1]['NAME']),
                    '{r21manager.dir_tel}' => Helper::utf8($r21manager[1]['N_TEL']),
                ];
            }
            if (isset($r21manager[2])) {
                $reg_params_ur['{r21manager.buh_pin}'] = Helper::utf8($r21manager[2]['PIN']);
                $reg_params_ur['{r21manager.buh}'] = Helper::utf8($r21manager[2]['NAME']);
                $reg_params_ur['{r21manager.buh_tel}'] = Helper::utf8($r21manager[2]['N_TEL']);
            }
        }

        $sql = getSQL('passport/get_r21stan_h.sql');
        $array = $this->db->selectRaw($sql, ['tin' => $tin, 'c_distr' => $dpi])->get();
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
            '{r21taxpay.d_old_dpi}' => $r21taxpay['D_OLD_DPI'],
            '{r21taxpay.d_new_dpi}' => $r21taxpay['D_NEW_DPI'],
            '{r21taxpay.old_dpi}' => $r21taxpay['OLD_DPI'],
            '{r21taxpay.new_dpi}' => $r21taxpay['NEW_DPI'],
        ];
        if ($r21taxpay['OLD_DPI']) {
            $old_dpi_name = $this->db->table('AISR.ais_spr_bd')
                ->where('kod_bdp = :kod_bdp')
                ->bind(['kod_bdp' => $r21taxpay['OLD_DPI']])->getCell('NAME');
            $reg_params['{r21taxpay.old_dpi_name}'] = Helper::utf8($old_dpi_name);
        }
        if ($r21taxpay['NEW_DPI']) {
            $new_dpi_name = $this->db->table('AISR.ais_spr_bd')
                ->where('kod_bdp = :kod_bdp')
                ->bind(['kod_bdp' => $r21taxpay['NEW_DPI']])->getCell('NAME');
            $reg_params['{r21taxpay.new_dpi_name}'] = Helper::utf8($new_dpi_name);
        }

        $array = $this->db->table('pass_sfp')
            ->where('tin = :tin')
            ->bind(['tin' => $params['TIN']])->get();
        $sfp = $this->transform($array, 'SFP.');

        $sql = 'SELECT * FROM pdv_act_r WHERE tin = :tin AND dat_anul IS NULL AND ROWNUM = 1';
        $pdv_act_r = $this->db->selectRaw($sql, $params)->first();
        if (!empty($pdv_act_r)) {$reg_params['{pdv_act_r.dat_reestr}'] = $pdv_act_r['DAT_REESTR'];}

        /* Види діяльності */
        $sql = getSQL('passport/kvedy.sql');
        $array = $this->db->selectRaw($sql, ['tin' => $params['TIN']])->get();
        $kvedy = $this->transform($array, 'KVED.');

        /* Засновники */
        $sql = getSQL('passport/founders.sql');
        $array = $this->db->selectRaw($sql, ['tin' => $params['TIN']])->get();
        $founders = $this->transform($array, 'FNDR.');

        /* РРО */
        $sql = getSQL('passport/rro.sql');
        $array = $this->db->selectRaw($sql, ['tin' => $params['TIN']])->get();
        $rro = $this->transform($array, 'RRO.');

        /* Об’єкти */
        $sql = getSQL('passport/get_taxobjects.sql');
        $array = $this->db->selectRaw($sql, ['tin' => $params['TIN']])->get();
        $objects = $this->transform($array, 'OBJ.');

        return array_merge($reg_params, $reg_params_ur, $stan_h, $sfp, $kvedy, $founders, $rro, $objects);
    }

    protected function getDefaultParams($params): array
    {
        /* Шаблон для вибору з листа ексель комірок {data} [data] [[data]] */
        $pattern = '@(\{[0-9a-zA-Z_.]+?\})|(\[\[#[0-9a-zA-Z_.]+?#\]\])|(\[#[0-9a-zA-Z_.]+?#\])@';
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
    protected function getSumFromArray(array $array, $find): array
    {
        if (empty($array)) {return [];}
        $find_array = explode('.', $find);
        $prefix = $find_array[0] . '.';
        $field = $find_array[1];
        $sum_name = '{' . $prefix . $field . '_SUM}';
        $sum = array_sum($array['#' . $prefix . $field . '#']);
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
                $result['#' . $prefix . $col . '#'][] = $value_utf8;
            }
        }
        return $result;
    }

}
