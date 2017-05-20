<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 19/05/17
 * Time: 18:13
 */

namespace CallCenter;


class KillProcess
{
    public static function kill($signal, $process_name)
    {
        self::closePreviousRunningProcesses($signal, $process_name);
    }

    private function closePreviousRunningProcesses($signal, $process_name)
    {
        foreach (self::getPreviousRunningProcesses($process_name) as $process_detail) {
            $process_id = [];
            if (preg_match("/\d+/", $process_detail, $process_id)) {
                shell_exec("kill -{$signal} {$process_id[0]}");
            }
        }
    }

    private function getPreviousRunningProcesses($process_name)
    {
        $list_previous_running_processes = shell_exec("ps fax | grep {$process_name} | grep -v grep");
        return explode("\n", $list_previous_running_processes);
    }
}