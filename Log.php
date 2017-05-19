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

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function log($message, $tipo, $id='')
    {
        $log_message = Date("[Y-m-d H:i:s]") . "[{$tipo}][{$id}]" . $message;
        file_put_contents($this->file, $log_message, FILE_APPEND | LOCK_EX);
    }

}