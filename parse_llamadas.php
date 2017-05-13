<?php
require "./Log.php";
require "./ParseCalls.php";
require "./CallCenterCalls.php";
require "./ManualCalls/ManualCalls.php";
require "./PredictiveCalls/PredictiveCalls.php";
require "./conectorDB.php";
require "./Record.php";
require "./ManualCalls/ManualRecord.php";
require "./PredictiveCalls/PredictiveRecord.php";

$log_predictive_call = new \CallCenter\Log('/var/log/microvoz/parseX4_llamadas_predictivas.log');
$log_manual_call = new \CallCenter\Log('/var/log/microvoz/parseX4_llamadas_manuales.log');

$db = new \CallCenter\ConectorDB('parse', 'Milicom.', 'OP');

$record_predictive = new \CallCenter\PredictiveCalls\PredictiveRecord($db, 'asterisk.cc_predictivo');
$predictive_call = new \CallCenter\PredictiveCalls\PredictiveCalls($record_predictive, $db, $log_predictive_call);

$record_manual = new \CallCenter\ManualCalls\ManualRecord($db, 'asterisk.cc_manual');
$manual_call = new \CallCenter\ManualCalls\ManualCalls($record_manual, $db, $log_manual_call);

$parse = new \CallCenter\ParseCalls();

$parse->start($manual_call, $predictive_call);