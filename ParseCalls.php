<?php
/**
 * Created by PhpStorm.
 * User: Gabriel Díaz <gabrieldiaz31@gmail.com>
 * Date: 08/05/17
 * Time: 11:53
 */

namespace CallCenter;


class ParseCalls
{
    protected $process_name = 'parseX4';
    protected $delay;

    public function __construct($delay)
    {
        $this->delay = $delay;
        $this->closePreviousRunningProcesses();
    }

    public function start(ManualCalls\ManualCalls $manual_calls, PredictiveCalls\PredictiveCalls $predictive_calls)
    {
        while (true) {
            $manual_calls->parseCall();
            $predictive_calls->parseCall();
            sleep($this->delay);
        }
    }

    private function closePreviousRunningProcesses()
    {
        foreach ($this->getPreviousRunningProcesses() as $process_detail) {
            $process_id = [];
            if (preg_match("/\d+/", $process_detail, $process_id)) {
                shell_exec("kill -KILL {$process_id[0]}");
            }
        }
    }

    private function getPreviousRunningProcesses()
    {
        $list_previous_running_processes = shell_exec("ps fax | grep {$this->process_name} | grep -v grep");
        return explode("\n", $list_previous_running_processes);
    }
}