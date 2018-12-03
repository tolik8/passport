<?php

namespace App\controllers;

use alhimik1986\PhpExcelTemplator\PhpExcelTemplator;

class Pasport
{
    protected $twig;
    protected $db;
    protected $myUser;
    protected $bc;
    protected $role = 22; // Роль 22 - Паспорт платника
    protected $x;
    protected $new_guid;

    public function __construct (\App\Twig $twig, \App\QueryBuilder $db, \App\MyUser $myUser, \App\Breadcrumb $bc)
    {
        $this->twig = $twig;
        $this->db = $db;
        $this->myUser = $myUser;
        $this->bc = $bc;
        $access = in_string($this->role, $this->myUser->roles);
        if (!$access) {
            $this->twig->showTemplate('index.html', ['my' => $this->myUser]); exit;
        }
        $this->x['title'] = 'Паспорт';
    }

    public function pasport ()
    {
        $this->x['menu'] = $this->bc->getMenu('pasport');
        $this->twig->showTemplate('pasport/pasport.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function getPost ()
    {
        $post = [];
        $pattern = '#^[0-9]{6,10}$#';
        $post['tin'] = regex($pattern, $_POST['tin'], 0);

        $pattern = '#^\s*(3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2})\s*$#';
        $post['dt1'] = regex($pattern, $_POST['dt1'], '01.01.2017');
        $post['dt2'] = regex($pattern, $_POST['dt2'], '31.12.2018');
        return $post;
    }

    public function check ()
    {
        $this->x['menu'] = $this->bc->getMenu('check');
        $this->x['post'] = $params = $this->getPost();

        $sql = file_get_contents('../sql/pasport/check.sql');
        $this->x['data'] = $this->db->getOneRowFromSQL($sql, $params);

        if (!$this->db->last_result) {
            $this->twig->showTemplate('error.html', ['x' => $this->x, 'my' => $this->myUser]); exit;
        }

        if ($this->x['data'] == false) {
            $_SESSION['post'] = $params;
            header('Location: /pasport/prepare');
        }

        $this->twig->showTemplate('pasport/check.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function prepare ()
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

        $this->prepareTable01($params);
        $this->prepareTable02($params);

        $this->db->insert('PIKALKA.pasp_log', [
            'guid' => $this->new_guid,
            'dt1' => $params['dt1'],
            'dt2' => $params['dt2'],
            'tin' => $params['tin'],
            'guid_user' => $this->myUser->guid
        ]);

        if (empty($this->x['prepare_errors'])) {
            $this->x['prepare_time'] = round(microtime(true) - $start_time, 4);
            $this->twig->showTemplate('pasport/prepared.html', ['x' => $this->x, 'my' => $this->myUser]);
        } else {
            $this->twig->showTemplate('error.html', ['x' => $this->x, 'my' => $this->myUser]);
        }
    }

    public function excel ()
    {
        $templateFile = '../xls/pasport/template.xlsx';
        $outputFile = './pasport.xlsx';
        $outputMethod = true;

        $pattern = '#^[0-9a-zA-Z]{32}$#';
        $this->new_guid = regex($pattern, $_POST['guid'], 0);

        $params = $this->db->getOneRow('PIKALKA.pasp_log', ['guid' => $this->new_guid]);

        $input_xlsParams = ['{tin}' => $params['TIN'], '{dt1}' => $params['DT1'], '{dt2}' => $params['DT2']];

        $reg_data = $this->excelRegData($params);
        $params_01 = $this->excelTable01($params);
        $params_02 = $this->excelTable02($params);

        $xlsParams = array_merge($input_xlsParams, $reg_data, $params_01, $params_02);

        if ($outputMethod)
            PhpExcelTemplator::outputToFile($templateFile, $outputFile, $xlsParams);
        else PhpExcelTemplator::saveToFile($templateFile, $outputFile, $xlsParams);

    }

    public function transform (array $array = [])
    {
        $result = [];
        if (empty($array)) return $result;
        $columns = array_keys($array[0]);

        foreach ($array as $row) {
            foreach ($columns as $col) {
                $value_utf8 = mb_convert_encoding($row[$col], "utf-8", "windows-1251");
                $result[$col][] = $value_utf8;
            }
        }
        return $result;
    }

    public function vidsFromArray (array $array = [], $precision = 0)
    {
        $result = [];
        if (empty($array)) return $result;
        $sum = array_sum($array);

        foreach ($array as $row) {
            $result[] = round($row / $sum * 100, $precision);
        }
        return $result;
    }

    public function prepareTable01 ($params)
    {
        $prepared = false;

        $params = array_merge($params, ['guid' => $this->new_guid]);

        $sql = file_get_contents('../sql/pasport/check_pasp_kontr_kre1.sql');
        $count1 = $this->db->getOneValueFromSQL($sql, $params);
        $sql = file_get_contents('../sql/pasport/check_pasp_kontr_kre2.sql');
        $count2 = $this->db->getOneValueFromSQL($sql, $params);

        if ($count1 > 0 and $count2 > 0) {
            $prepared = true;
            return $prepared;
        }

        if ($count1 === '0') {
            $sql = file_get_contents('../sql/pasport/insert_pasp_kontr_kre1.sql');
            $this->db->insertFromSQL($sql, $params);
        }

        if ($count2 === '0') {
            $sql = file_get_contents('../sql/pasport/insert_pasp_kontr_kre2.sql');
            $this->db->insertFromSQL($sql, $params);
        }

        if ($this->db->errors_count == 0) {
            $prepared = true;
        } else {
            $this->x['prepare_errors'][] = 'таблиця 1';
        }

        return $prepared;
    }

    public function prepareTable02 ($params)
    {
        $prepared = false;

        $params = array_merge($params, ['guid' => $this->new_guid]);

        $sql = file_get_contents('../sql/pasport/check_pasp_kontr_zob1.sql');
        $count1 = $this->db->getOneValueFromSQL($sql, $params);
        $sql = file_get_contents('../sql/pasport/check_pasp_kontr_zob2.sql');
        $count2 = $this->db->getOneValueFromSQL($sql, $params);

        if ($count1 > 0 and $count2 > 0) {
            $prepared = true;
            return $prepared;
        }

        if ($count1 === '0') {
            $sql = file_get_contents('../sql/pasport/insert_pasp_kontr_zob1.sql');
            $this->db->insertFromSQL($sql, $params);
        }

        if ($count2 === '0') {
            $sql = file_get_contents('../sql/pasport/insert_pasp_kontr_zob2.sql');
            $this->db->insertFromSQL($sql, $params);
        }

        if ($this->db->errors_count == 0) {
            $prepared = true;
        } else {
            $this->x['prepare_errors'][] = 'таблиця 2';
        }

        return $prepared;
    }

    public function excelRegData ($input_params)
    {
        $params['TIN'] = $input_params['TIN'];
        $sql = file_get_contents('../sql/pasport/get_r21taxpay.sql');
        $r21taxpay = $this->db->getOneRowFromSQL($sql, $params);
        $stan_name = $this->db->getOneValue('N_STAN','E_S_STAN', ['c_stan' => $r21taxpay['C_STAN']]);
        $kved_name = $this->db->getOneValue('NU','E_KVED', ['kod' => $r21taxpay['KVED']]);
        $sql = file_get_contents('../sql/pasport/get_r21paddr.sql');
        $address = $this->db->getOneValueFromSQL($sql, $params);
        $sql = file_get_contents('../sql/pasport/get_r21manager.sql');
        $r21manager = $this->db->getKeyValuesFromSQL($sql, $params);
        $sql = file_get_contents('../sql/pasport/get_pdv_act_r.sql');
        $pdv_act_r = $this->db->getOneRowFromSQL($sql, $params);
        $xls_params = [
            '{r21taxpay.c_distr}' => $r21taxpay['C_DISTR'],
            '{r21taxpay.name}' => utf8($r21taxpay['NAME']),
            '{r21taxpay.stan}' => $r21taxpay['C_STAN'],
            '{r21taxpay.stan_name}' => utf8($stan_name),
            '{r21taxpay.kved}' => $r21taxpay['KVED'],
            '{r21taxpay.kved_name}' => utf8($kved_name),
            '{r21taxpay.d_reg_sti}' => $r21taxpay['D_REG_STI'],
            '{r21paddr.address}' => utf8($address),
            '{r21manager.dir}' => utf8($r21manager[1]['NAME']),
            '{r21manager.buh}' => utf8($r21manager[2]['NAME']),
            '{r21manager.dir_tel}' => utf8('тел. ' . $r21manager[1]['N_TEL']),
            '{r21manager.buh_tel}' => utf8('тел. ' . $r21manager[2]['N_TEL']),
            '{pdv_act_r.dat_reestr}' => $pdv_act_r['DAT_REESTR'],
        ];

        return $xls_params;
    }

    public function excelTable01 ($params)
    {
        $sql = file_get_contents('../sql/pasport/kontr_kre.sql');
        $array = $this->db->getAllFromSQL($sql, $params);

        $kontr = $this->transform($array);

        if (empty($kontr)) {
            $fields = ['N', 'STI', 'TIN', 'NAME', 'OBS', 'PDV', 'NOM'];
            foreach ($fields as $value) $kontr[$value] = [];
        }

        $kontr['OBS_SUM'] = array_sum($kontr['OBS']);
        $kontr['PDV_SUM'] = array_sum($kontr['PDV']);
        $kontr['OBS_VIDS'] = $this->vidsFromArray($kontr['OBS']);

        $xls_params = [
            '[kontr1.n]' => $kontr['N'],
            '[kontr1.sti]' => $kontr['STI'],
            '[kontr1.tin]' => $kontr['TIN'],
            '[kontr1.name]' => $kontr['NAME'],
            '[kontr1.obs]' => $kontr['OBS'],
            '[kontr1.pdv]' => $kontr['PDV'],
            '[kontr1.nom]' => $kontr['NOM'],
            '[kontr1.vids]' => $kontr['OBS_VIDS'],
            '{kontr1.obs_sum}' => $kontr['OBS_SUM'],
            '{kontr1.pdv_sum}' => $kontr['PDV_SUM'],
        ];
        return $xls_params;
    }

    public function excelTable02 ($params)
    {
        $sql = file_get_contents('../sql/pasport/kontr_zob.sql');
        $array = $this->db->getAllFromSQL($sql, $params);

        $kontr = $this->transform($array);

        if (empty($kontr)) {
            $fields = ['N', 'STI', 'TIN', 'NAME', 'OBS', 'PDV', 'NOM'];
            foreach ($fields as $value) $kontr[$value] = [];
        }

        $kontr['OBS_SUM'] = array_sum($kontr['OBS']);
        $kontr['PDV_SUM'] = array_sum($kontr['PDV']);
        $kontr['OBS_VIDS'] = $this->vidsFromArray($kontr['OBS']);

        $xls_params = [
            '[kontr2.n]' => $kontr['N'],
            '[kontr2.sti]' => $kontr['STI'],
            '[kontr2.tin]' => $kontr['CP_TIN'],
            '[kontr2.name]' => $kontr['NAME'],
            '[kontr2.obs]' => $kontr['OBS'],
            '[kontr2.pdv]' => $kontr['PDV'],
            '[kontr2.nom]' => $kontr['NOM'],
            '[kontr2.vids]' => $kontr['OBS_VIDS'],
            '{kontr2.obs_sum}' => $kontr['OBS_SUM'],
            '{kontr2.pdv_sum}' => $kontr['PDV_SUM'],
        ];
        return $xls_params;
    }
}