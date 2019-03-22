<?php

namespace App\controllers;

use alhimik1986\PhpExcelTemplator\PhpExcelTemplator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Passport extends Controller
{
    protected $role = '22'; // Роль 22 - Паспорт платника
    protected $title = 'Паспорт';

    public function index (): void
    {
        $this->x['menu'] = $this->bc->getMenu('passport');
        $this->twig->showTemplate('passport/index.html', ['x' => $this->x, 'my' => $this->myUser]);
        if (DEBUG) {d($this);}
    }

    public function choice (): void
    {
        $this->x['menu'] = $this->bc->getMenu('choice');
        $this->x['post'] = $params = $this->getPost();

        $this->x['info'] = $this->db->getAll('PIKALKA.d_pass_info', [], 'id');

        $this->twig->showTemplate('passport/choice.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function check (): void
    {
        $this->x['menu'] = $this->bc->getMenu('check');
        $this->x['post'] = $params = $this->getPost();

        $sql = file_get_contents($this->root . '/sql/passport/check.sql');
        $this->x['data'] = $this->db->getOneRowFromSQL($sql, $params);

        if (!$this->db->resultIsOk) {
            $this->twig->showTemplate('error.html', ['x' => $this->x, 'my' => $this->myUser]); Exit;
            /** @noinspection PhpUnreachableStatementInspection */
            if (DEBUG) {d($this);}
        }

        if (empty($this->x['data'])) {
            $_SESSION['post'] = $params;
            // Passport not found, prepare passport
            header('Location: /passport/prepare');
            exit;
        } else {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if ($this->x['data']['TM'] === null) {
                // Passport created at the moment
                header('Location: /passport/loading/'.$this->x['data']['GUID']);
                exit;
            }
        }
        // Passport exists, show the choice between "Use existing" and "Generate new"
        $this->twig->showTemplate('passport/check.html', ['x' => $this->x, 'my' => $this->myUser]);
        if (DEBUG) {d($this);}
    }

    public function loading ($guid): void
    {
        $this->x['menu'] = $this->bc->getMenu('loading');
        $loading_index = 1;
//        $loading_index = random_int(1,12);
//        echo $loading_index;
        if ($loading_index < 10) {$this->x['loading_index'] = 'a0'.$loading_index;} else {$this->x['loading_index'] = 'a'.$loading_index;}

        $this->x['guid'] = $guid;
        $this->twig->showTemplate('passport/loading.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function ajax_ ($guid): void
    {
        $params = ['guid' => $guid];
        $sql = 'SELECT COUNT(*) FROM PIKALKA.pass_jrn WHERE guid = :guid AND tm IS NOT NULL';
        $cnt = $this->db->getOneValueFromSQL($sql, $params);
        if ($cnt === '1') {
            $tm = $this->db->getOneValue('tm', 'PIKALKA.pass_jrn', $params);
            echo 'FINISH ' . $tm;
        } else {
            $sql = 'SELECT * FROM PIKALKA.pass_steps WHERE guid = :guid and step > 0 ORDER BY step';
            $this->x['steps'] = $this->db->getAllFromSQL($sql, ['guid' => $guid]);
            $this->twig->showTemplate('passport/ajax.html', ['x' => $this->x]);
        }
    }

    public function ajax ($guid): void
    {
        $sql = 'SELECT work_id, tm FROM PIKALKA.pass_work WHERE guid = :guid ORDER BY work_id';
        $this->x['works'] = $this->db->getAllFromSQL($sql, ['guid' => $guid]);
//        $sql = file_get_contents($this->root . '/sql/passport/get_work_info.sql');
//        $this->x['works'] = ArrayToUtf8($this->x['works']);
        echo json_encode($this->x['works']);
    }

    public function prepare (): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->x['data'] = $params = $this->getPost();
            $work = [];
            foreach ($_POST as $key => $post) {
                if (strpos($key,'id') === 0) {$work[] = substr($key,2);}
            }
            $work = implode(',', $work);
        } else {
            if (!isset($_SESSION['post'])) {header('Location: /passport'); exit;}
            $params = $_SESSION['post'];
            unset($_SESSION['post']);
        }
        $count = $this->db->getCount('RG02.r21taxpay', ['tin' => $params['tin']]);
        if ($count === 0) {
            header('Location: /passport/taxpayer_not_found');
        } else {
            $new_guid = $this->db->getNewGUID();
            if (DEBUG) {$package = 'passport_dev';} else {$package = 'passport';}
            $sql = 'BEGIN '.$package.'.create_job(:tin, :dt1, :dt2, :work, :user_guid, :guid); END;';
            $params = array_merge($params, ['work' => $work, 'user_guid' => $this->myUser->guid, 'guid' => $new_guid]);
            $this->db->runSQL($sql, $params);
            header('Location: /passport/loading/' . $new_guid);
        }
        exit;
    }

    public function work (): void
    {
        $this->x['menu'] = $this->bc->getMenu('work');
        $loading_index = 1;
//        $loading_index = random_int(1,12);
//        echo $loading_index;
        if ($loading_index < 10) {$this->x['loading_index'] = 'a0'.$loading_index;} else {$this->x['loading_index'] = 'a'.$loading_index;}

        $work = [];
        foreach ($_POST as $key => $post) {
            if (strpos($key,'id') === 0) {$work[] = substr($key,2);}
        }
        $work = implode(',', $work);
        $sql = "SELECT id, name FROM PIKALKA.d_pass_info WHERE INSTR(',' || '" . $work . "' || ',', ',' || id || ',') > 0";
        $this->x['works'] = $this->db->getAllFromSQL($sql);

        $this->twig->showTemplate('passport/work.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function toExcel (): void
    {
        $templateFile = $this->root . '/xls/passport/template.xlsx';
        $outputFile = './passport.xlsx';
        $outputMethod = true;
        $default_params = $templateVars = [];

        try {$this->ss = IOFactory::load($templateFile);}
        catch (\Exception $e) {echo $e->getMessage(); Exit;}

        try {
            for ($i = 0; $i <= $this->ss->getSheetCount()-1; $i++) {
                $templateVars[$i+1] = $this->ss->getSheet($i)->toArray();
                $default_params[$i+1] = $this->prepareParams($templateVars[$i+1]);
            }
        } catch (\Exception $e) {echo $e->getMessage(); Exit;}

        $pattern = '#^[0-9a-zA-Z]{32}$#';
        $this->new_guid = regex($pattern, $_POST['guid'], 0);

        $params = $this->db->getOneRow('PIKALKA.pass_jrn', ['guid' => $this->new_guid]);
        $guid_param = ['guid' => $this->new_guid];

        $input_xlsParams = ['{tin}' => $params['TIN'], '{dt1}' => $params['DT1'], '{dt2}' => $params['DT2']];

        // Реєстраційні дані
        $reg_data = $this->excelRegData($params);
        $params_from_ora = array_merge($input_xlsParams, $reg_data);
        try {$sheet1 = $this->ss->getSheet(0);}
        catch (\Exception $e) {echo $e->getMessage(); Exit;}
        $templateParams = array_merge($default_params[1], $params_from_ora);
        PhpExcelTemplator::renderWorksheet($sheet1, $templateVars[1], $templateParams);

        // Контрагенти
        $sql = 'SELECT ROWNUM n, t.* FROM (SELECT * FROM PIKALKA.pass_kontr_kredit_3 WHERE guid = :guid ORDER BY obs DESC, tin) t';
        $params_01 = $this->excelKontr($sql, $params, 'T1.');
        $sql = 'SELECT ROWNUM n, t.* FROM (SELECT * FROM PIKALKA.pass_kontr_zobov_3 WHERE guid = :guid ORDER BY obs DESC, cp_tin) t';
        $params_02 = $this->excelKontr($sql, $params, 'T2.');
        $params_from_ora = array_merge($params_01, $params_02);
        try {$sheet2 = $this->ss->getSheet(1);}
        catch (\Exception $e) {echo $e->getMessage(); Exit;}
        $templateParams = array_merge($default_params[2], $params_from_ora);
        PhpExcelTemplator::renderWorksheet($sheet2, $templateVars[2], $templateParams);

        // Баланс
        $array = $this->db->getAll('PIKALKA.pass_balance', $guid_param, 'period_year, period_month');
        $this->setSheet(3, $array);

        // Прибуток
        $array = $this->db->getAll('PIKALKA.pass_pributok', $guid_param, 'period_year, period_month');
        $this->setSheet(4, $array);

        // ЄСВ
        $array = $this->db->getAll('PIKALKA.pass_esv', $guid_param, 'period');
        $this->setSheet(5, $array);

        // Пов’язані
        $sql = 'SELECT t.*, \'\' blank FROM PIKALKA.pass_pov_t1 t WHERE guid = :guid ORDER BY c_distr, tin, c_stan';
        $array1 = $this->db->getAllFromSQL($sql, $guid_param);
        $array1 = $this->transform1($array1);
        $array1 = $this->transform2($array1, 'T1.');

        $array2 = $this->db->getAll('PIKALKA.pass_pov_t2', $guid_param, 't DESC, tin, c_distr, c_stan, c_post');
        $array2 = $this->transform1($array2);
        $array2 = $this->transform2($array2, 'T2.');

        $array3 = $this->db->getAll('PIKALKA.pass_pov_t3', $guid_param, 't DESC, tin, c_distr, c_stan, c_post');
        $array3 = $this->transform1($array3);
        $array3 = $this->transform2($array3, 'T3.');

        $array4 = $this->db->getAll('PIKALKA.pass_pov_t4', $guid_param, 't DESC, tin, c_distr, c_stan, c_post');
        $array4 = $this->transform1($array4);
        $array4 = $this->transform2($array4, 'T4.');

        $array5 = $this->db->getAll('PIKALKA.pass_pov_t5', $guid_param, 't DESC, tin, c_distr, c_stan');
        $array5 = $this->transform1($array5);
        $array5 = $this->transform2($array5, 'T5.');

        $params_from_ora = array_merge($array1, $array2, $array3, $array4, $array5);
        try {$sheet6 = $this->ss->getSheet(5);}
        catch (\Exception $e) {echo $e->getMessage(); Exit;}
        $templateParams = array_merge($default_params[6], $params_from_ora);
        PhpExcelTemplator::renderWorksheet($sheet6, $templateVars[6], $templateParams);

        // ПДВ
        $array = $this->db->getAll('PIKALKA.pass_pdv', $guid_param, 'period');
        $this->setSheet(7, $array);

        // ПДВ РІК
        $array = $this->db->getAll('PIKALKA.pass_pdv_rik', $guid_param, 'period_year');
        $this->setSheet(8, $array);

        // Повідомлення
        $array = $this->db->getAll('PIKALKA.pass_povidom', $guid_param, 'period_year');
        $this->setSheet(9, $array);

        // Реєстр ПДВ
        $array = $this->db->getAll('AISR.pdv_act_r', ['tin' => $params['TIN']], 'dat_svd');
        $this->setSheet(10, $array);

        // Засновники
        $sql = 'SELECT SUM(sum_infund) FROM rg02.r21pfound WHERE tin = :tin';
        $sum_infund = $this->db->getOneValueFromSQL($sql, $params);
        $sql = file_get_contents($this->root . '/sql/passport/founders.sql');
        $tmp_params = array_merge($params, ['sum_infund' => $sum_infund]);
        $array = $this->db->getAllFromSQL($sql, $tmp_params);
        $this->setSheet(11, $array);

        // запис в pass_log
        $sql_params = [
            'guid' => $this->new_guid, 'dt1' => $params['DT1'], 'dt2' => $params['DT2'],
            'tin' => $params['TIN'], 'guid_user' => $this->myUser->guid, 'tm' => 0,
        ];
        $this->db->insert('PIKALKA.pass_log', $sql_params);

        if ($outputMethod) {PhpExcelTemplator::outputSpreadsheetToFile($this->ss, $outputFile);}
        else {PhpExcelTemplator::saveSpreadsheetToFile($this->ss, $outputFile);}
    }

    public function taxpayer_not_found (): void
    {
        $this->x['errors'][] = 'Вказаний платник не знайдений в базі даних';
        $this->twig->showTemplate('error.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    protected function setSheet ($index, $array): void
    {
        $array = $this->transform1($array);
        $params_from_ora = $this->transform2($array);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $sheet = $this->ss->getSheet($index - 1);
            /** @noinspection PhpUndefinedMethodInspection */
            $templateVars = $sheet->toArray();
            $default_params = $this->prepareParams($templateVars);
        } catch (\Exception $e) {
            echo $e->getMessage(); Exit;
        }
        $templateParams = array_merge($default_params, $params_from_ora);
        PhpExcelTemplator::renderWorksheet($sheet, $templateVars, $templateParams);
    }

    protected function excelRegData ($input_params): array
    {
        $tin = $params['TIN'] = $input_params['TIN'];

        $sql = 'SELECT PIKALKA.get_dpi_by_tin(:tin) FROM dual';
        $dpi = $this->c_distr = $this->db->getOneValueFromSQL($sql, $params);
        $type_pl = (int) $this->db->getOneValue('FACE_MODE','RG02.r21taxpay', ['tin' => $tin, 'c_distr' => $dpi]);

        $r21taxpay = $this->db->getOneRow('RG02.r21taxpay', ['tin' => $tin, 'c_distr' => $dpi]);

        $stan_name = $this->db->getOneValue('N_STAN','ETALON.E_S_STAN', ['c_stan' => $r21taxpay['C_STAN']]);
        $kved_name = $this->db->getOneValue('NU','ETALON.E_KVED', ['kod' => $r21taxpay['KVED']]);

        $sql = 'SELECT AISR.rpp_util.getfulladdress(c_city,t_street,c_street,house,house_add,unit,apartment) adr '.chr(10).
            'FROM RG02.r21paddr WHERE tin = :tin AND c_distr = :c_distr AND c_adr = 1';
        $address = $this->db->getOneValueFromSQL($sql, ['tin' => $tin, 'c_distr' => $dpi]);

        if ($type_pl === 1) {
            $sql = 'SELECT c_post, pin, name, n_tel FROM RG02.r21manager WHERE tin = :tin';
            $r21manager = $this->db->getKeyValuesFromSQL($sql, $params);
            $reg_params_ur = [
                '{r21manager.dir}' => utf8($r21manager[1]['NAME']),
                '{r21manager.buh}' => utf8($r21manager[2]['NAME']),
                '{r21manager.dir_tel}' => utf8($r21manager[1]['N_TEL']),
                '{r21manager.buh_tel}' => utf8($r21manager[2]['N_TEL']),
            ];
        } else {
            $reg_params_ur = [];
        }

        $sql = file_get_contents($this->root . '/sql/passport/get_r21stan_h.sql');
        $array = $this->db->getAllFromSQL($sql, ['tin' => $tin, 'c_distr' => $dpi]);
        $array = $this->transform1($array);
        $stan_h = $this->transform2($array, 'SH.');

        $reg_params = [
            '{r21taxpay.c_distr}' => $this->c_distr,
            '{r21taxpay.name}' => utf8($r21taxpay['NAME']),
            '{r21taxpay.stan}' => $r21taxpay['C_STAN'],
            '{r21taxpay.stan_name}' => utf8($stan_name),
            '{r21taxpay.kved}' => $r21taxpay['KVED'],
            '{r21taxpay.kved_name}' => utf8($kved_name),
            '{r21taxpay.d_reg_sti}' => $r21taxpay['D_REG_STI'],
            '{r21paddr.address}' => utf8($address),
        ];

        $sql = 'SELECT * FROM AISR.pdv_act_r WHERE tin = :tin AND dat_anul IS NULL AND ROWNUM = 1';
        $pdv_act_r = $this->db->getOneRowFromSQL($sql, $params);
        if (!empty($pdv_act_r)) {$reg_params['{pdv_act_r.dat_reestr}'] = $pdv_act_r['DAT_REESTR'];}

        return array_merge($reg_params, $reg_params_ur, $stan_h);
    }

    protected function excelKontr ($sql, $params, $prefix): array
    {
        $array = $this->db->getAllFromSQL($sql, $params);
        $array = $this->transform1($array);

        if (empty($array)) {
            $fields = ['N', 'STI', 'TIN', 'NAME', 'OBS', 'PDV', 'NOM'];
            foreach ($fields as $value) {$array[$value] = [];}
        }

        $obs_sum = array_sum($array['OBS']);
        $pdv_sum = array_sum($array['PDV']);
        $array['VIDS'] = $this->vidsFromArray($array['OBS']);

        $kontr = $this->transform2($array, $prefix);
        $calculate_params = ['{'.$prefix.'OBS_SUM}' => $obs_sum, '{'.$prefix.'PDV_SUM}' => $pdv_sum];

        return array_merge($kontr, $calculate_params);
    }

    protected function transform1 (array $array = []): array
    {
        $result = [];
        if (empty($array)) {return $result;}
        $columns = array_keys($array[0]);

        foreach ($array as $row) {
            foreach ($columns as $col) {
                $value_utf8 = mb_convert_encoding($row[$col], 'utf-8', 'windows-1251');
                $result[$col][] = $value_utf8;
            }
        }
        return $result;
    }

    protected function transform2 (array $array = [], $prefix = ''): array
    {
        $result = [];
        if (empty($array)) {return $result;}

        foreach ($array as $key => $value) {$result['['.$prefix.$key.']'] = $value;}

        return $result;
    }

    protected function vidsFromArray (array $array = [], $precision = 0): array
    {
        $result = [];
        if (empty($array)) {return $result;}
        $sum = array_sum($array);

        foreach ($array as $row) {
            $result[] = round($row / $sum * 100, $precision);
        }
        return $result;
    }

    protected function getPost (): array
    {
        $post = [];
        $pattern = '#^[0-9]{6,10}$#';
        $post['tin'] = regex($pattern, $_POST['tin'], 0);

        $pattern = '#^\s*(3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2})\s*$#';
        $post['dt1'] = regex($pattern, $_POST['dt1'], '01.01.2017');
        $post['dt2'] = regex($pattern, $_POST['dt2'], '31.12.2018');
        return $post;
    }

    protected function prepareParams ($params): array
    {
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
}