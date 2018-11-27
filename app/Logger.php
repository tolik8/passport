<?php

namespace App;

class Logger
{
    protected $Directory;
    protected $Extension;
    protected $SaveToFile;

    public function __construct ()
    {
        $logger_config = include __DIR__ . '/../config/logger.php';
        $this->Directory  = $logger_config['Directory'];
        $this->Extension  = $logger_config['Extension'];
        $this->SaveToFile = $logger_config['SaveToFile'];
    }

    public function save (array $debugs, array $data) 
    {
        if (!$this->SaveToFile) {exit;}

        $p = chr(13).chr(10);
        $content = '';

        $filename = $this->Directory . '/' . date('Y-m-d') . ' ' . date('His') . '.' . $this->Extension;
        
        $content .= 'FILE: ' . $debugs[0]['file'] . $p;
        $content .= 'LINE: ' . $debugs[0]['line'] . $p;
        $content .= 'Class: ' . $debugs[0]['class'] . $p;
        $content .= 'Function: ' . $debugs[0]['function'] . $p;
        $content .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . $p.$p;
        
        foreach ($data as $item) {
            if (!is_array($item)) {
                $content .= $item . $p.$p;
            } else {
                foreach ($item as $key => $value) {
                    $content .= ($key .': '. $value) . $p;
                }
            }
        }

        $log_filename = $_SERVER['DOCUMENT_ROOT'] . '/' . $filename;
        $result = @file_put_contents($log_filename, $content, FILE_APPEND);
        if (!$result) echo 'Error writing file: ' . $log_filename;
    }
}