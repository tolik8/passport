<?php

namespace App\controllers;

use alhimik1986\PhpExcelTemplator\PhpExcelTemplator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Pasport
{
    private $root;
    protected $twig;
    protected $db;
    protected $myUser;
    protected $bc;
    protected $role = '22'; // Роль 22 - Паспорт платника
    protected $x;
    protected $new_guid;
    protected $c_distr;

    public function __construct (\App\Twig $twig, \App\QueryBuilder $db, \App\MyUser $myUser, \App\Breadcrumb $bc)
    {
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        $this->twig = $twig;
        $this->db = $db;
        $this->myUser = $myUser;
        $this->bc = $bc;
        $access = in_string($this->role, $this->myUser->roles);
        if (!$access) {$this->twig->showTemplate('index.html', ['my' => $this->myUser]); Exit;}
        $this->x['title'] = 'Паспорт';
    }

    public function pasport (): void
    {
        $this->x['menu'] = $this->bc->getMenu('pasport');
        $this->twig->showTemplate('pasport/pasport.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function check (): void
    {
        $this->x['menu'] = $this->bc->getMenu('check');
        $this->x['post'] = $params = $this->getPost();

        $sql = file_get_contents($this->root . '/sql/pasport/check.sql');
        $this->x['data'] = $this->db->getOneRowFromSQL($sql, $params);

        if (!$this->db->resultIsOk) {
            $this->twig->showTemplate('error.html', ['x' => $this->x, 'my' => $this->myUser]); Exit;
        }

        if (empty($this->x['data'])) {
            $_SESSION['post'] = $params;
            header('Location: /pasport/prepare');
        }

        $this->twig->showTemplate('pasport/check.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function prepare (): void
    {
        $start_time = microtime(true);
        $this->x['menu'] = $this->bc->getMenu('prepare');
        $this->x['guid'] = $this->new_guid = $this->db->getNewGUID();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->x['data'] = $params = $this->getPost();
        } else {
            if (!isset($_SESSION['post'])) {header('Location: /pasport');}
            $params = $_SESSION['post'];
            unset($_SESSION['post']);
        }

        // Підготовка даних - Контрагенти (кредит)
        $this->prepareKontr($params, 'kre');
        // Підготовка даних - Контрагенти (зобов’язання)
        $this->prepareKontr($params, 'zob');
        // Підготовка даних - Баланс
        $this->prepareBalance($params);

        $sql_params = [
            'guid' => $this->new_guid, 'dt1' => $params['dt1'], 'dt2' => $params['dt2'],
            'tin' => $params['tin'], 'guid_user' => $this->myUser->guid,
        ];

        // запис в post_jrn
        $this->db->insert('PIKALKA.pasp_jrn', $sql_params);

        $this->x['prepare_time'] = round(microtime(true) - $start_time, 4);
        $sql_params['tm'] = str_replace('.', ',', $this->x['prepare_time']);

        // запис в post_log
        $this->db->insert('PIKALKA.pasp_log', $sql_params);

        if (empty($this->x['prepare_errors'])) {
            $this->twig->showTemplate('pasport/prepared.html', ['x' => $this->x, 'my' => $this->myUser]);
        } else {
            $this->twig->showTemplate('error.html', ['x' => $this->x, 'my' => $this->myUser]);
        }
    }

    public function toExcel (): void
    {
        $templateFile = '../xls/pasport/template.xlsx';
        $outputFile = './pasport.xlsx';
        $outputMethod = true;
        $templateVars = [];

        try {$spreadsheet = IOFactory::load($templateFile);}
        catch (\Exception $e) {echo $e->getMessage(); Exit;}

        try {
            for ($i = 0; $i <= $spreadsheet->getSheetCount()-1; $i++)
            {$templateVars[$i+1] = $spreadsheet->getSheet($i)->toArray();}
        } catch (\Exception $e) {echo $e->getMessage(); Exit;}

        $pattern = '#^[0-9a-zA-Z]{32}$#';
        $this->new_guid = regex($pattern, $_POST['guid'], 0);

        $params = $this->db->getOneRow('PIKALKA.pasp_jrn', ['guid' => $this->new_guid]);
        $guid_param = ['guid' => $this->new_guid];

        $input_xlsParams = ['{tin}' => $params['TIN'], '{dt1}' => $params['DT1'], '{dt2}' => $params['DT2']];

        // Реєстраційні дані
        $reg_data = $this->excelRegData($params);
        $params_from_ora = array_merge($input_xlsParams, $reg_data);
        try {$sheet1 = $spreadsheet->getSheet(0);}
        catch (\Exception $e) {echo $e->getMessage(); Exit;}
        $default_params = $this->prepareParams($templateVars[1]);
        $templateParams = array_merge($default_params, $params_from_ora);
        PhpExcelTemplator::renderWorksheet($sheet1, $templateVars[1], $templateParams);

        // Контрагенти
        $sql = file_get_contents($this->root . '/sql/pasport/kontr_kre.sql');
        $params_01 = $this->excelKontr($sql, $params, 'T1.');
        $sql = file_get_contents($this->root . '/sql/pasport/kontr_zob.sql');
        $params_02 = $this->excelKontr($sql, $params, 'T2.');
        $params_from_ora = array_merge($params_01, $params_02);
        try {$sheet2 = $spreadsheet->getSheet(1);}
        catch (\Exception $e) {echo $e->getMessage(); Exit;}
        $default_params = $this->prepareParams($templateVars[2]);
        $templateParams = array_merge($default_params, $params_from_ora);
        PhpExcelTemplator::renderWorksheet($sheet2, $templateVars[2], $templateParams);

        // Баланс
        $array = $this->db->getAll('PIKALKA.pasp_balance', $guid_param, 'period_year, period_month');
        $array = $this->transform1($array);
        $params_from_ora = $this->transform2($array);
        try {$sheet3 = $spreadsheet->getSheet(2);}
        catch (\Exception $e) {echo $e->getMessage(); Exit;}
        $default_params = $this->prepareParams($templateVars[3]);
        $templateParams = array_merge($default_params, $params_from_ora);
        PhpExcelTemplator::renderWorksheet($sheet3, $templateVars[3], $templateParams);

        // запис в post_log
        $sql_params = [
            'guid' => $this->new_guid, 'dt1' => $params['DT1'], 'dt2' => $params['DT2'],
            'tin' => $params['TIN'], 'guid_user' => $this->myUser->guid, 'tm' => 0,
        ];
        $this->db->insert('PIKALKA.pasp_log', $sql_params);

        if ($outputMethod) {PhpExcelTemplator::outputSpreadsheetToFile($spreadsheet, $outputFile);}
        else {PhpExcelTemplator::saveSpreadsheetToFile($spreadsheet, $outputFile);}
    }

    protected function prepareKontr ($params, $type): bool
    {
        $prepared = false;

        $guid_param = ['guid' => $this->new_guid];
        $params = array_merge($params, $guid_param);

        $count1 = $this->db->getCount('PIKALKA.pasp_kontr_'.$type.'1', $guid_param);
        $count2 = $this->db->getCount('PIKALKA.pasp_kontr_'.$type.'2', $guid_param);

        if ($count1 > 0 && $count2 > 0) {$prepared = true; return $prepared;}

        if ($count1 === 0) {
            $sql = file_get_contents($this->root . '/sql/pasport/insert_pasp_kontr_'.$type.'1.sql');
            $this->db->runSQL($sql, $params);
        }

        if ($count2 === 0) {
            $sql = file_get_contents($this->root . '/sql/pasport/insert_pasp_kontr_'.$type.'2.sql');
            $this->db->runSQL($sql, $params);
        }

        if ($this->db->errors_count === 0) {$prepared = true;}
        else {$this->x['prepare_errors'][] = 'Контрагенти - Таблиця ' . $type;}

        return $prepared;
    }

    protected function prepareBalance ($params): bool
    {
        $prepared = false;

        $guid_param = ['guid' => $this->new_guid];
        $params = array_merge($params, $guid_param);

        $count1 = $this->db->getCount('PIKALKA.pasp_balance', $guid_param);
        if ($count1 > 0) {$prepared = true; return $prepared;}

        if ($count1 === 0) {
            $sql = file_get_contents($this->root . '/sql/pasport/insert_pasp_balance.sql');
            $this->db->runSQL($sql, $params);
        }

        if ($this->db->errors_count === 0) {$prepared = true;}
        else {$this->x['prepare_errors'][] = 'Баланс';}

        return $prepared;
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

        $sql = file_get_contents($this->root . '/sql/pasport/get_r21stan_h.sql');
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