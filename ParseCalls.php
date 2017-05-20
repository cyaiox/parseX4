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
        KillProcess::kill('KILL', $this->process_name);
    }

    public function start(
        ManualCalls\ManualCalls $manual_calls,
        PredictiveCalls\PredictiveCalls $predictive_calls,
        InboundCalls\InboundCalls $inbound_calls,
        IvrCalls\IvrCalls $ivr_calls
    ) {
        while (true) {
            $manual_calls->parseCall();
            $predictive_calls->parseCall();
            $inbound_calls->parseCall();
            $ivr_calls->parseCall();
            sleep($this->delay);
        }
    }
}