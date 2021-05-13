<?php

namespace App\controllers;

use App\Helper;
use App\Config;

class Passport extends DBController
{
    protected $role = '22'; // Роль 22 - Паспорт платника
    protected $title = 'Паспорт платника';

    public function index(): void
    {
        $this->x['menu'] = $this->bc->getMenu('index');

        $sql = getSQL('passport\check_user_jobs.sql');
        $this->x['jobs'] = $this->db->selectRaw($sql)->get();
        //$this->x['jobs'] = require(ROOT . '/config/job_data.php');

        foreach ($this->x['jobs'] as $key => $value) {
            $this->x['jobs'][$key]['WHAT'] = $this->parseOracleJobString($value['WHAT']);
        }

        if (PASSPORT_ENABLE) {
            $this->twig->showTemplate('passport/index.html', ['x' => $this->x, 'my' => $this->myUser]);
        } else {
            $this->twig->showTemplate('passport/index_disable.html', ['x' => $this->x, 'my' => $this->myUser]);
        }
    }

    public function choice(): void
    {
        $this->x['menu'] = $this->bc->getMenu('choice');
        $params = $this->getPost();
        $params['user_guid'] = $this->myUser->guid;

        $taxpay_name = $this->tax->getName($params['tin']);
        if ($taxpay_name === null) {
            exit('Error! Taxpay not found! - Помилка! платник не знайдений!');
        }
        $this->x['name'] = $taxpay_name;

        $portable_mode = Config::get('settings.PORTABLE_MODE');
        if ($portable_mode) {
            $sql = getSQL('passport\tasks.sql');
        } else {
            $sql = getSQL('passport\access_tasks.sql');
        }
        $this->x['info'] = $this->db->selectRaw($sql, $params)->get();

        $this->x['post'] = $params;
        $this->twig->showTemplate('passport/choice.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function prepare(): void
    {
        //$this->db->enableQueryLog('query');
        $this->x['menu'] = $this->bc->getMenu('prepare');
        $params = $this->x['post'] = $this->getPost();
        $this->x['info_is_not_ready'] = true;

        $this->x['name'] = $this->tax->getName($params['tin']);

//        $this->x['loading_index'] = 'a101';
        try {
            $this->x['loading_index'] = 'a' . random_int(101, 112);
        } catch (\Exception $e) {
            $this->x['loading_index'] = 'a101';
        }

        $task = $ready = $refresh = [];

        foreach ($_POST as $key => $post) {
            if (strpos($key,'id') === 0) {
                $id = substr($key,2);
                $task[] = $id;
                if (array_key_exists('ir' . $id, $_POST) && $_POST['ir' . $id] !== '') {$ready[] = $id;}
                if (array_key_exists('rf' . $id, $_POST) && $_POST['rf' . $id] === 'on') {$refresh[] = $id;}
            }
        }
        $task_string = implode(',', $task);
        $ready_string = implode(',', $ready);
        $refresh_string = implode(',', $refresh);

        if ($task_string === $ready_string && $refresh_string === '') {
            $this->x['info_is_not_ready'] = false;
        }

        $portable_mode = Config::get('settings.PORTABLE_MODE');
        if ($portable_mode) {
            $sql = getSQL('passport/tasks_portable.sql');
        } else {
            $sql = getSQL('passport/selected_tasks.sql');
        }
        $data = ['guid' => $this->myUser->guid, 'task' => $task_string];
        $this->x['tasks'] = $this->db->selectRaw($sql, $data)->get();

        if ($this->x['info_is_not_ready']) {
//            $guid = $this->x['GUID'] = 'test';
            $new_guid = $this->x['guid'] = $this->db->getNewGUID();
            if (DEBUG) {$package = 'passport_dev';} else {$package = 'passport';}
            $sql = 'BEGIN ' . $package . '.create_job(:tin, :dt1, :dt2, :ip, :task, :refresh, :user_guid, :guid); END;';
            $params = array_merge($params, [
                'task'      => $task_string,
                'refresh'   => $refresh_string,
                'user_guid' => $this->myUser->guid,
                'guid'      => $new_guid,
                'ip'        => $_SERVER['REMOTE_ADDR']
            ]);
            $this->db->statement($sql, $params);
        }
        $this->x['task'] = $task_string;

        $this->twig->showTemplate('passport/prepare.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function ajax($guid): void
    {
        $sql = getSQL('passport/get_task_info.sql');
        $this->x['tasks'] = $this->db->selectRaw($sql, ['guid' => $guid])->get();

        /* Якщо в масиві буде текст, то додатково застосувати функцію ArrayToUtf8 */
        echo json_encode($this->x['tasks']);
    }

    public function taxpayer_not_found(): void
    {
        $this->x['errors'][] = 'Вказаний платник не знайдений в базі даних';
        $this->twig->showTemplate('error.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    protected function getPost(): array
    {
        $post['tin'] = Helper::checkRegEx('tin', trim($_POST['tin']), 0);
        $post['dt1'] = Helper::checkRegEx('date', $_POST['dt1'], '01.01.2018');
        $post['dt2'] = Helper::checkRegEx('date', $_POST['dt2'], '31.12.2020');

        return $post;
    }

    private function parseOracleJobString($input)
    {
        if ($input === null || empty($input)) {return null;}
        $pattern = "#(?<=\().*(?=\))#";
        preg_match($pattern, $input, $matches);
        $matches_array = explode(' ', $matches[0]);
        $count_matches_array = count($matches_array);
        $matches_array[0] = substr($matches_array[0], 0, -1);
        $matches_array[$count_matches_array-1] .= ',';
        for ($i = 1; $i < $count_matches_array; $i++) {
            $matches_array[$i] = substr($matches_array[$i], 1, -2);
        }
        $matches_array['NAME'] = $this->tax->getName($matches_array[0]);

        return $matches_array;
    }
}
