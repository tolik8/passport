<?php

namespace App;

class Logger
{
    protected $Directory;
    protected $Extension;
    protected $SaveToFile;

    public function __construct ()
    {
        //$logger_config = require $_SERVER['DOCUMENT_ROOT'] . '/config/logger.php';
        $logger_config = require '../config/logger.php';
        $this->Directory  = $logger_config['Directory'];
        $this->Extension  = $logger_config['Extension'];
        $this->SaveToFile = $logger_config['SaveToFile'];
    }

    public function save (array $debugs, array $data): void
    {
        if (!$this->SaveToFile) {exit;}

        $p = chr(13).chr(10);
        $content = '';
        $line = [];

        $filename = $this->Directory . '/' . date('Y-m-d') . ' ' . date('His') . '.' . $this->Extension;
        $content .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . $p.$p;

        foreach ($debugs as $key => $item) {
            if (!isset($item['class'])) {continue;}

            if ($item['class'] !== 'DI\Container' && $item['class'] !== 'Invoker\Invoker') {
                if (!isset($item['function'])) {$function_name = '';} else {$function_name = $item['function'];}
                if (!isset($debugs[$key - 1]['line'])) {$line_number = '';} else {$line_number = $debugs[$key - 1]['line'];}
                $line[] = $item['class'] . '->' . $function_name . ' ' . $line_number . $p;
            }
        }
        $line = array_reverse($line);
        foreach ($line as $item) {$content .= $item;}

        $content .= $p;

        foreach ($data as $item) {
            if (!is_array($item)) {
                $content .= $item . $p.$p;
            } else {
                foreach ($item as $key => $value) {
                    $content .= ($key .': '. $value) . $p;
                }
            }
        }

        $content .= '====================================================================' . $p;

        $log_filename = $_SERVER['DOCUMENT_ROOT'] . '/' . $filename;
        $result = @file_put_contents($log_filename, $content, FILE_APPEND);
        if (!$result) {echo 'Error writing file: ' . $log_filename;}
    }
}