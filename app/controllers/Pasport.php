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

    public function __construct (\App\Twig $twig, \App\QueryBuilder $db, \App\MyUser $myUser, \App\Breadcrumb $bc)
    {
        $this->twig = $twig;
        $this->db = $db;
        $this->myUser = $myUser;
        $this->bc = $bc;
        $access = in_string($this->role, $this->myUser->roles);
        if (!$access) {
            $this->twig->showTemplate('index.html', ['my' => $this->myUser]);
            exit;
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

        $this->twig->showTemplate('pasport/check.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function prepare ()
    {
        $start_time = microtime(true);
        $this->x['menu'] = $this->bc->getMenu('prepare');

        $this->x['data'] = $post = $this->getPost();

        $params = ['tin' => $post['tin'], 'dt1' => $post['dt1'], 'dt2' => $post['dt2'], 'guid' => $this->myUser->guid];

        $new_guid = $this->db->getNewGUID();

        $this->prepareTable01($params);

        $this->db->insert('PIKALKA.pasp_log', [
            'guid' => $new_guid,
            'dt1' => $post['dt1'],
            'dt2' => $post['dt2'],
            'tin' => $post['tin'],
            'guid_user' => $this->myUser->guid
        ]);

        if (empty($this->x['prepare_errors'])) {
            $this->x['prepare_time'] = round(microtime(true) - $start_time, 4);
            $this->twig->showTemplate('pasport/prepared.html', ['x' => $this->x, 'my' => $this->myUser]);
        } else {
            $this->twig->showTemplate('pasport/not_pasport.html', ['x' => $this->x, 'my' => $this->myUser]);
        }
    }

    public function excel ()
    {
        $templateFile = '../xls/pasport/template.xlsx';
        $outputFile = './pasport.xlsx';

        $post = $this->getPost();
        $params = ['tin' => $post['tin'], 'dt1' => $post['dt1'], 'dt2' => $post['dt2'], 'guid' => $this->myUser->guid];

        $input_params = ['{tin}' => $post['tin'], '{dt1}' => $post['dt1'], '{dt2}' => $post['dt2']];

        $params_01 = $this->excelTable01($params);

        $xlsParams = array_merge($input_params, $params_01);

        // PhpExcelTemplator::saveToFile($templateFile, $outputFile, $xlsParams);
        PhpExcelTemplator::outputToFile($templateFile, $outputFile, $xlsParams);
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

        $sql = file_get_contents('../sql/pasport/check_knopka_s01.sql');
        $count1 = $this->db->getOneValueFromSQL($sql, $params);
        $sql = file_get_contents('../sql/pasport/check_knopka_s02.sql');
        $count2 = $this->db->getOneValueFromSQL($sql, $params);

        if ($count1 > 0 and $count2 > 0) {
            $prepared = true;
            return $prepared;
        }
    
        if ($count1 === '0') {
            $sql = file_get_contents('../sql/pasport/insert_into_knopka_s01.sql');
            $this->db->insertFromSQL($sql, $params);
        }
    
        if ($count2 === '0') {
            $sql = file_get_contents('../sql/pasport/insert_into_knopka_s02.sql');
            $this->db->insertFromSQL($sql, $params);
        }

        if ($this->db->errors_count == 0) {
            $prepared = true;
        } else {
            $this->x['prepare_errors'][] = 'таблиця 1';
        }

        return $prepared;
    }

    public function excelTable01 ($params)
    {
        $sql = file_get_contents('../sql/pasport/kontragent_kredit.sql');
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
            '[kontr_n]' => $kontr['N'],
            '[kontr_sti]' => $kontr['STI'],
            '[kontr_tin]' => $kontr['TIN'],
            '[kontr_name]' => $kontr['NAME'],
            '[kontr_obs]' => $kontr['OBS'],
            '[kontr_pdv]' => $kontr['PDV'],
            '[kontr_nom]' => $kontr['NOM'],
            '[kontr_vids]' => $kontr['OBS_VIDS'],
            '{kontr_obs_sum}' => $kontr['OBS_SUM'],
            '{kontr_pdv_sum}' => $kontr['PDV_SUM'],
        ];
        return $xls_params;
    }

}