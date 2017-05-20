<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 10/05/17
 * Time: 10:08
 */

namespace CallCenter;


class Log
{
    protected $file;
    protected $global_log = '/var/log/microvoz/parseX4.log';
    protected $debug;

    public function __construct($file, $debug=false)
    {
        $this->file = $file;
        $this->debug = $debug;
    }

    public function log($message, $tipo, $id='')
    {
        $log_message = Date("[Y-m-d H:i:s]") . "[{$tipo}][{$id}] $message\n";
        file_put_contents($this->file, $log_message, FILE_APPEND | LOCK_EX);
        file_put_contents($this->global_log, $log_message, FILE_APPEND | LOCK_EX);
        if ($this->debug) {
            echo $log_message;
        }
    }

}